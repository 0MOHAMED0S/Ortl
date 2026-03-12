<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlotBooking;
use App\Models\CallSession;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StudentBookingController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }
    public function getStudentBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $now = Carbon::now();

            // جلب الحجوزات مع تفاصيل الموعد والمعلم
            $allBookings = SlotBooking::with(['slot.teacher.user'])
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->get();

            $processedBookings = $allBookings->map(function ($booking) use ($user, $now) {
                $slot = $booking->slot;
                if (!$slot) return null;

                $teacher = $slot->teacher;
                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                // جلب الجلسة المرتبطة بهذا الحجز تحديداً لجلب الرابط
                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $slot->teacher_id)
                    ->where('started_at', $slotStartDateTime->toDateTimeString())
                    ->first();

                $isPast = $slotEndDateTime->isPast() || $booking->status === 'completed' || optional($callSession)->status === 'ended';

                return [
                    'booking_id'     => $booking->id,
                    'status'         => $booking->status,
                    'slot_details'   => [
                        'id'         => $slot->id,
                        'date'       => $slot->date,
                        'start_time' => $slot->start_time,
                        'end_time'   => $slot->end_time,
                    ],
                    'teacher' => [
                        'name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                        'photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    ],
                    'session_record' => [
                        // 🚀 الرابط الذي طلبته يظهر هنا
                        'recording_url' => optional($callSession)->recording_url,
                        'is_available'  => !empty(optional($callSession)->recording_url),
                    ],
                    'is_past' => $isPast
                ];
            })->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع سجل الحجوزات بنجاح.',
                'data'    => [
                    'upcoming' => $processedBookings->where('is_past', false)->values(),
                    'history'  => $processedBookings->where('is_past', true)->values(),
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ في الخادم.'], 500);
        }
    }
    public function joinBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id'
        ]);

        $user = auth()->user();

        try {
            $call = CallSession::with('teacher.user')->findOrFail($request->call_session_id);
            if ($call->student_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بالانضمام لهذه المكالمة.'], 403);
            }
            if (!str_contains($call->channel_name, 'scheduled_call')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عذراً، هذا المسار مخصص للمواعيد المحجوزة مسبقاً فقط.'
                ], 400);
            }
            if ($call->status === 'ended') {
                return response()->json(['status' => false, 'message' => 'هذه المكالمة انتهت مسبقاً.'], 400);
            }
            $now = Carbon::now();
            $startTime = Carbon::parse($call->started_at);

            if ($now->copy()->addMinutes(5)->lessThan($startTime)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكنك الانضمام للجلسة الآن. يُسمح بالدخول قبل الموعد بـ 5 دقائق كحد أقصى.'
                ], 400);
            }
            if (in_array($call->status, ['initiated', 'scheduled'])) {
                $call->update(['status' => 'ongoing']);
            }
            $token = $this->agoraService->generateToken($call->channel_name, $user->id, 'publisher');
            try {
                $teacherUser = $call->teacher->user;
                if ($teacherUser) {
                    $notificationData = [
                        'call_session_id' => $call->id,
                        'channel_name'    => $call->channel_name,
                        'student_name'    => $user->name,
                    ];

                    // 1. إرسال البث اللحظي (Pusher) لتطبيق المعلم
                    broadcast(new \App\Events\StudentJoinedSession($call->teacher_id, $notificationData));

                    // 2. حفظ الإشعار في الداتابيز
                    $teacherUser->notify(new \App\Notifications\DynamicNotification(
                        'الطالب في انتظارك ⏳',
                        "الطالب {$user->name} انضم الآن إلى الجلسة المجدولة.",
                        'student_joined',
                        $notificationData
                    ));
                }
            } catch (\Exception $e) {
                Log::error('Teacher Join Notification Error: ' . $e->getMessage());
            }
            // ==========================================

            return response()->json([
                'status'  => true,
                'message' => 'تم الانضمام للغرفة بنجاح.',
                'data'    => [
                    'call_session_id' => $call->id,
                    'channel_name'    => $call->channel_name,
                    'agora_token'     => $token,
                    'uid'             => $user->id,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Join Booked Session Error (Student): ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
    public function leaveBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id'
        ]);

        $user = auth()->user();

        try {
            $call = CallSession::with('teacher.user')->findOrFail($request->call_session_id);
            if ($call->student_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك.'], 403);
            }
            if ($call->status === 'ended') {
                return response()->json(['status' => true, 'message' => 'الجلسة منتهية بالفعل.']);
            }
            try {
                $teacherUser = $call->teacher->user;
                if ($teacherUser) {
                    $notificationData = [
                        'call_session_id' => $call->id,
                        'student_name'    => $user->name,
                        'event'           => 'student_left'
                    ];
                    broadcast(new \App\Events\StudentLeftSession($call->teacher_id, $notificationData));
                }
            } catch (\Exception $e) {
                Log::error('Teacher Leave Notification Error: ' . $e->getMessage());
            }
            // ==========================================

            return response()->json([
                'status'  => true,
                'message' => 'تمت المغادرة بنجاح. يمكنك العودة للجلسة طالما أنها مستمرة.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Leave Booked Session Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء محاولة المغادرة.'
            ], 500);
        }
    }
}

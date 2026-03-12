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
    public function getUpcomingBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $now = Carbon::now();

            $bookings = SlotBooking::with(['slot.teacher.user'])
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('slot_bookings.user_id', $user->id)
                ->where('slot_bookings.status', 'scheduled')
                // جلب المواعيد التي تنتهي في المستقبل فقط
                ->where(function ($query) use ($now) {
                    $query->where('teacher_slots.date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('teacher_slots.date', $now->toDateString())
                                ->where('teacher_slots.end_time', '>', $now->toTimeString());
                        });
                })
                ->orderBy('teacher_slots.date', 'asc')
                ->orderBy('teacher_slots.start_time', 'asc')
                ->select('slot_bookings.*')
                ->get();

            $data = $bookings->map(function ($booking) use ($user, $now) {
                $slot = $booking->slot;
                $teacher = $slot->teacher;
                $slotStart = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEnd = Carbon::parse($slot->date . ' ' . $slot->end_time);

                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStart->toDateTimeString())
                    ->first();

                // التحقق من إمكانية الانضمام (قبل 5 دقائق وحتى النهاية)
                $canJoin = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStart)
                    && $now->lessThanOrEqualTo($slotEnd);

                return [
                    'booking_id'   => $booking->id,
                    'slot_id'      => $slot->id,
                    'date'         => $slot->date,
                    'start_time'   => $slot->start_time,
                    'end_time'     => $slot->end_time,
                    'teacher_name' => optional($teacher->user)->name ?? 'معلم ورتل',
                    'teacher_photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    'call_id'      => optional($callSession)->id,
                    'channel_name' => optional($callSession)->channel_name,
                    'can_join'     => $canJoin,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الحجوزات القادمة بنجاح.',
                'data'    => $data
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Upcoming Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب البيانات.'], 500);
        }
    }
    public function getBookingsHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 10);

            $history = SlotBooking::with(['slot.teacher.user'])
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('slot_bookings.user_id', $user->id)
                // الحجوزات المكتملة أو التي مضى وقتها
                ->where(function ($query) {
                    $query->where('slot_bookings.status', 'completed')
                        ->orWhere('teacher_slots.date', '<', now()->toDateString());
                })
                ->orderBy('teacher_slots.date', 'desc')
                ->orderBy('teacher_slots.start_time', 'desc')
                ->select('slot_bookings.*')
                ->paginate($perPage);

            $history->getCollection()->transform(function ($booking) use ($user) {
                $slot = $booking->slot;
                $teacher = $slot->teacher;
                $slotStart = Carbon::parse($slot->date . ' ' . $slot->start_time);

                // جلب الجلسة للحصول على رابط التسجيل
                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStart->toDateTimeString())
                    ->first();

                return [
                    'booking_id'    => $booking->id,
                    'slot_id'       => $slot->id,
                    'date'          => $slot->date,
                    'time'          => $slot->start_time . ' - ' . $slot->end_time,
                    'teacher_name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                    'status'        => $booking->status,
                    'recording_url' => optional($callSession)->recording_url, // 🚀 رابط التسجيل
                    'duration'      => optional($callSession)->duration_minutes,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع سجل الحجوزات بنجاح.',
                'data'    => $history
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Bookings History Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب السجل.'], 500);
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

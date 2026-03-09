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
            $allBookings = SlotBooking::with(['slot.teacher.user'])
                ->where('slot_bookings.user_id', $user->id)
                ->where('slot_bookings.status', '!=', 'cancelled')
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->orderBy('teacher_slots.date', 'desc') // الأحدث أولاً
                ->orderBy('teacher_slots.start_time', 'desc')
                ->select('slot_bookings.*')
                ->get();

            $processedBookings = $allBookings->map(function ($booking) use ($user, $now) {
                $slot = $booking->slot;
                if (!$slot || !$slot->teacher) return null;

                $teacher = $slot->teacher;
                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStartDateTime->toDateTimeString())
                    ->whereIn('status', ['initiated', 'scheduled', 'ongoing', 'ended'])
                    ->first();
                $isPast = $slotEndDateTime->isPast() || optional($callSession)->status === 'ended' || $booking->status === 'completed';
                $canJoin = !$isPast && $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
                    && $now->lessThanOrEqualTo($slotEndDateTime);

                return [
                    'booking_id'       => $booking->id,
                    'date'             => $slot->date,
                    'start_time'       => $slot->start_time,
                    'end_time'         => $slot->end_time,
                    'full_date_time'   => $slotStartDateTime->toDateTimeString(),
                    'status'           => $booking->status, // scheduled, completed, etc.
                    'is_past'          => $isPast,
                    'teacher' => [
                        'id'    => $teacher->id,
                        'name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                        'photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    ],
                    'call_details' => [
                        'id'           => optional($callSession)->id,
                        'channel_name' => optional($callSession)->channel_name,
                        'status'       => optional($callSession)->status,
                        'can_join'     => $canJoin,
                    ]
                ];
            })->filter()->values();

            // 3️⃣ تقسيم البيانات إلى قادم وسابق
            $upcoming = $processedBookings->where('is_past', false)->values();
            $history  = $processedBookings->where('is_past', true)->values();

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الحجوزات بنجاح.',
                'data'    => [
                    'upcoming' => $upcoming,
                    'history'  => $history
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Student Bookings Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
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
}

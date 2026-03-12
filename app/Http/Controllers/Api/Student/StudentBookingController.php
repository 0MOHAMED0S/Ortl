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
   public function getStudentSessionHistory(Request $request)

    {

        try {

            $userId = auth()->id();

            $perPage = $request->query('per_page', 10);

            $history = RecitationSession::where('status', 'ended')

                ->whereHas('attendees', function ($query) use ($userId) {

                    $query->where('user_id', $userId);

                })

                ->with(['teacher.user', 'attendees' => function ($query) use ($userId) {

                    $query->where('user_id', $userId);

                }])

                ->orderBy('start_at', 'desc') // الأحدث أولاً

                ->paginate($perPage);

            $history->getCollection()->transform(function ($session) {

                $attendance = $session->attendees->first();

                $teacher = $session->teacher;

                $teacherUser = optional($teacher)->user;

                $teacherName = $teacherUser->name ?? 'معلم ورتل';



                return [

                    'id'                => $session->id,

                    'title'             => $session->title,

                    'teacher_name'      => $teacherName,

                    'teacher_avatar'    => ($teacher && $teacher->profile_photo_path)

                        ? asset('storage/' . $teacher->profile_photo_path)

                        : 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=0d9488&color=fff',

                    'start_at'          => $session->start_at->format('Y-m-d H:i:s'),

                    'end_at'            => $session->end_at->format('Y-m-d H:i:s'),

                    'recording_url'     => $session->recording_url,

                    'my_attendance'     => [

                        'joined_at'        => optional($attendance)->joined_at ? $attendance->joined_at->format('H:i:s') : null,

                        'left_at'          => optional($attendance)->left_at ? $attendance->left_at->format('H:i:s') : null,

                        'duration_minutes' => (optional($attendance)->joined_at && optional($attendance)->left_at)

                            ? round($attendance->joined_at->diffInMinutes($attendance->left_at), 2)

                            : 0,

                    ]

                ];

            });



            return response()->json([

                'status'  => true,

                'message' => 'تم استرجاع سجل الحصص بنجاح.',

                'data'    => $history

            ]);

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error("Get Student History Error: " . $e->getMessage());

            return response()->json([

                'status'  => false,

                'message' => 'فشل جلب سجل الحصص.',

                'error'   => config('app.debug') ? $e->getMessage() : null

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

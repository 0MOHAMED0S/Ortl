<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use App\Models\Session_student;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSessionController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * انضمام المعلم للحصة (كـ Host)
     * يمكنه الدخول والخروج في أي وقت طالما وقت الحصة لم ينتهِ
     */
    public function startSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $session = RecitationSession::with('teacher')->findOrFail($sessionId);

            // 1. التحقق من صلاحية المعلم
            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بدخول هذه الحصة.'
                ], 403);
            }

            // 2. التحقق من وقت انتهاء الحصة
            if ($now->gt($session->end_at)) {
                // إذا انتهى الوقت، يتم إغلاق الحصة تلقائياً
                if ($session->status !== 'ended') {
                    $session->update(['status' => 'ended']);
                }
                return response()->json([
                    'status' => false,
                    'message' => 'انتهى الوقت المخصص لهذه الحصة.'
                ], 400);
            }

            // 3. تحديث حالة الحصة إلى Live إذا كانت Scheduled
            if ($session->status === 'scheduled') {
                $session->update([
                    'status' => 'live',
                    'actual_started_at' => $now
                ]);
            }

            // 4. توليد توكن Agora كـ Host
            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'host'
            );

            return response()->json([
                'status' => true,
                'message' => 'تم دخول الحصة بنجاح.',
                'data' => [
                    'agora_token'  => $token,
                    'channel_name' => $session->channel_name,
                    'app_id'       => config('services.agora.app_id'),
                    'uid'          => $userId,
                    'role'         => 'host'
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل دخول الحصة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * انضمام الطالب للحصة (كـ Subscriber)
     */
    public function joinSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $session = RecitationSession::findOrFail($sessionId);

            // 1. التحقق من وقت انتهاء الحصة
            if ($now->gt($session->end_at)) {
                if ($session->status !== 'ended') {
                    $session->update(['status' => 'ended']);
                }
                return response()->json([
                    'status' => false,
                    'message' => 'هذه الحصة انتهت زمنياً.'
                ], 400);
            }

            // 2. السماح للطالب بالدخول حتى لو لم يدخل المعلم بعد (طالما أن وقتها قد حان)
            // نتحقق فقط من أن وقت البداية قد حان (بفارق 5 دقائق قبلها مثلاً)
            if ($now->lt($session->start_at->subMinutes(5))) {
                return response()->json([
                    'status' => false,
                    'message' => 'وقت الحصة لم يحن بعد.'
                ], 403);
            }

            // 3. توليد التوكن للطالب
            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'subscriber'
            );

            // 4. تسجيل الدخول في جدول الحضور
            Session_student::updateOrCreate(
                [
                    'recitation_session_id' => $session->id,
                    'user_id' => $userId,
                    'left_at' => null // لتجنب إنشاء سجل جديد إذا كان موجوداً بالفعل ولم يغادر
                ],
                [
                    'joined_at' => clone $now // استخدام clone لتجنب مشاكل المراجع
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'تم الانضمام للحصة بنجاح.',
                'data' => [
                    'agora_token'  => $token,
                    'channel_name' => $session->channel_name,
                    'app_id'       => config('services.agora.app_id'),
                    'uid'          => (int)$userId,
                    'role'         => 'subscriber'
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل الانضمام للحصة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    /**
     * تسجيل مغادرة الطالب للحصة
     */
    public function leaveSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $attendance = Session_student::where('recitation_session_id', $sessionId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->latest()
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يوجد سجل حضور نشط.'
                ], 404);
            }

            $attendance->update([
                'left_at' => clone $now
            ]);

            $duration = $attendance->joined_at->diffInMinutes($now);

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل المغادرة بنجاح.',
                'data' => [
                    'duration_minutes' => $duration
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل تسجيل المغادرة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * إنهاء المعلم للحصة مبكراً (أو إجبارياً)
     */
    public function endSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $session = RecitationSession::with('teacher')->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بإنهاء هذه الحصة.'
                ], 403);
            }

            if ($session->status === 'ended') {
                return response()->json([
                    'status' => false,
                    'message' => 'الحصة مغلقة مسبقاً.'
                ]);
            }

            return DB::transaction(function () use ($session, $sessionId, $now) {

                // 1. تغيير حالة الحصة
                $session->update(['status' => 'ended']);

                // 2. تسجيل خروج لجميع الطلاب الذين لم يغادروا
                $affected = Session_student::where('recitation_session_id', $sessionId)
                    ->whereNull('left_at')
                    ->update(['left_at' => clone $now]);

                return response()->json([
                    'status' => true,
                    'message' => 'تم إنهاء الحصة بنجاح.',
                    'summary' => [
                        'force_logged_out' => $affected,
                        'ended_at' => $now->format('H:i:s')
                    ]
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل إنهاء الحصة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getAttendance($sessionId)
    {
        try {
            $userId = auth()->id();

            $session = RecitationSession::with('teacher')
                ->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بعرض كشف الحضور.'
                ], 403);
            }

            $attendees = Session_student::with('student:id,name,email')
                ->where('recitation_session_id', $sessionId)
                ->get()
                ->map(function ($record) {

                    $joined = $record->joined_at;
                    $left = $record->left_at ?? now();

                    $duration = $joined
                        ? $joined->diffInSeconds($left)
                        : 0;

                    return [
                        'id' => $record->id,
                        'student_name' => $record->student->name ?? 'N/A',
                        'student_email' => $record->student->email ?? null,
                        'joined_at' => $joined?->format('H:i:s'),
                        'left_at' => $record->left_at
                            ? $record->left_at->format('H:i:s')
                            : 'متصل حالياً',
                        'duration_minutes' => round($duration / 60, 2),
                        'is_present' => is_null($record->left_at),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب كشف الحضور بنجاح.',
                'session_title' => $session->title,
                'summary' => [
                    'total_students' => $attendees->count(),
                    'active_now' => $attendees->where('is_present', true)->count(),
                ],
                'data' => $attendees
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل جلب كشف الحضور.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getAllSessionsForStudent(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $sessions = RecitationSession::with(['teacher.user'])
                ->whereIn('status', ['live', 'upcoming', 'scheduled']) // تم إضافة scheduled
                ->where('end_at', '>', now())
                ->orderBy('start_at', 'asc')
                ->paginate($perPage);

            $sessions->getCollection()->transform(function ($session) {

                $isJoinable = $session->status === 'live' ||
                    (now()->between(
                        $session->start_at->subMinutes(10),
                        $session->end_at
                    ));

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'teacher_name' => $session->teacher->user->name ?? 'N/A',
                    'status' => $session->status,
                    'start_at' => $session->start_at,
                    'end_at' => $session->end_at,
                    'is_joinable' => $isJoinable,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب الحصص بنجاح.',
                'data' => $sessions
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل جلب الحصص.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getTeacherSessions(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->teacherProfile) {
                return response()->json([
                    'status' => false,
                    'message' => 'الحساب غير مرتبط بملف معلم.'
                ], 404);
            }

            $perPage = $request->query('per_page', 10);

            $sessions = RecitationSession::where(
                'teacher_id',
                $user->teacherProfile->id
            )
                ->with('teacher.user')
                ->orderBy('start_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'تم جلب حصص المعلم بنجاح.',
                'data' => $sessions
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل جلب الحصص.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use App\Models\Session_student; // استخدام الموديل الجديد
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
     * المعلم يبدأ الحصة
     */
    public function startSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();

            $session = RecitationSession::with('teacher')->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك ببدء هذه الحصة.'
                ], 403);
            }

            if ($session->status === 'ended') {
                return response()->json([
                    'status' => false,
                    'message' => 'هذه الحصة منتهية بالفعل.'
                ], 400);
            }

            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'host'
            );

            $session->update([
                'status' => 'live',
                'actual_started_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم بدء الحصة بنجاح.',
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
                'message' => 'فشل بدء الحصة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    /**
     * الطالب ينضم للحصة ويسجل وقت الدخول
     */
    public function joinSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $session = RecitationSession::findOrFail($sessionId);

            if ($now->gt($session->end_at)) {
                $session->update(['status' => 'ended']);

                return response()->json([
                    'status' => false,
                    'message' => 'هذه الحصة انتهت زمنياً.'
                ], 400);
            }

            if ($session->status !== 'live') {
                return response()->json([
                    'status' => false,
                    'message' => 'الحصة لم تبدأ بعد.'
                ], 403);
            }

            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'subscriber'
            );

            Session_student::updateOrCreate(
                [
                    'recitation_session_id' => $session->id,
                    'user_id' => $userId
                ],
                [
                    'joined_at' => $now,
                    'left_at' => null
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'تم الانضمام للحصة بنجاح.',
                'data' => [
                    'agora_token' => $token,
                    'channel_name' => $session->channel_name,
                    'app_id' => config('services.agora.app_id'),
                    'uid' => (int)$userId
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
     * تسجيل وقت خروج الطالب أو المعلم عند إغلاق القناة
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
                'left_at' => $now
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
     * المعلم ينهي الحصة ويغلقها على الجميع
     */
    public function endSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();

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

            return DB::transaction(function () use ($session, $sessionId) {

                $now = now();

                $session->update(['status' => 'ended']);

                $affected = Session_student::where('recitation_session_id', $sessionId)
                    ->whereNull('left_at')
                    ->update(['left_at' => $now]);

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


    /**
     * جلب كشف الحضور للحصة (خاص بالمعلم)
     */
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


    /**
     * جلب جميع الحصص المتاحة للطلاب (القادمة والجارية)
     */
    public function getAllSessionsForStudent(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $sessions = RecitationSession::with(['teacher.user'])
                ->whereIn('status', ['live', 'upcoming'])
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


    /**
     * جلب الحصص الخاصة بالمعلم المسجل دخوله حالياً
     */
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

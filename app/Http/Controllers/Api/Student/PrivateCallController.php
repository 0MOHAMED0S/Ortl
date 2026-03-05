<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Teacher;
use App\Models\UserPackage;
use App\Services\AgoraService;
use App\Events\IncomingPrivateCall;
use App\Models\RecitationSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\DynamicNotification;

class PrivateCallController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }
    private function getActivePackages($userId, $lockForUpdate = false)
    {
        $query = UserPackage::where('user_id', $userId)
            ->where('status', 'active') // Strict check
            ->where('remaining_minutes', '>', 0)
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
            })
            ->orderByRaw('expires_at IS NULL ASC, expires_at ASC'); // Non-null (expiring soon) first

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get();
    }
    private function calculateTotalMinutes($userId)
    {
        return UserPackage::where('user_id', $userId)
            ->whereIn('status', ['active', 'Active'])
            ->where('remaining_minutes', '>', 0)
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
            })
            ->sum('remaining_minutes');
    }
    public function startCall(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $user = auth()->user();
        $totalAvailableMinutes = $this->calculateTotalMinutes($user->id);

        if ($totalAvailableMinutes <= 0) {
            return response()->json([
                'status'  => false,
                'message' => 'ليس لديك رصيد دقائق كافٍ لإجراء المكالمة.'
            ], 400);
        }

        $teacher = Teacher::with('user')->findOrFail($request->teacher_id);

        if (!$teacher->is_online) {
            return response()->json([
                'status'  => false,
                'message' => 'عذراً، المعلم غير متصل حالياً ولا يمكنه استقبال المكالمات الفورية.'
            ], 400);
        }
        $isBusyInCall = CallSession::where('teacher_id', $teacher->id)
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere(function ($q) {
                        $q->where('status', 'initiated')
                            ->where('created_at', '>=', now()->subMinutes(2)); // حماية المعلم من التعليق للأبد
                    });
            })
            ->exists();

        $isBusyInLiveSession = RecitationSession::where('teacher_id', $teacher->id)
            ->where('status', 'live')
            ->exists();

        $mustJoinSession = RecitationSession::where('teacher_id', $teacher->id)
            ->whereIn('status', ['scheduled', 'upcoming'])
            ->where('start_at', '<=', now()->addMinutes(15)) // يمكنك تغيير الـ 15 دقيقة حسب رغبتك
            ->where('end_at', '>', now())
            ->exists();

        if ($isBusyInCall || $isBusyInLiveSession || $mustJoinSession) {
            return response()->json([
                'status'  => false,
                'message' => 'عذراً، المعلم مشغول حالياً (في حصة، مكالمة، أو لديه حصة ستبدأ فوراً). يرجى المحاولة لاحقاً.'
            ], 400);
        }

        $channelName = 'private_call_' . $user->id . '_' . $teacher->id . '_' . time();

        $call = CallSession::create([
            'student_id'   => $user->id,
            'teacher_id'   => $teacher->id,
            'channel_name' => $channelName,
            'status'       => 'initiated',
            'started_at'   => null,
        ]);

        $token = $this->agoraService->generateToken($channelName, $user->id, 'publisher');

        $callData = [
            'call_id'      => $call->id,
            'channel_name' => $channelName,
            'student_name' => $user->name,
        ];

        try {
            broadcast(new IncomingPrivateCall($teacher->id, $callData));

            if ($teacher->user) {
                $teacher->user->notify(new DynamicNotification(
                    'مكالمة واردة 📞',
                    'الطالب ' . $user->name . ' يتصل بك الآن.',
                    'incoming_call',
                    $callData
                ));
            }
        } catch (\Exception $e) {
            Log::error('Notification/Broadcast Error: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم بدء الاتصال، في انتظار رد المعلم.',
            'data'    => [
                'call_id'             => $call->id,
                'channel_name'        => $channelName,
                'agora_token'         => $token,
                'uid'                 => $user->id,
                'max_minutes_allowed' => (int) $totalAvailableMinutes
            ]
        ]);
    }
    public function joinCall(Request $request, $callId)
    {
        $teacherUserId = auth()->id();
        $call = CallSession::with('teacher')->findOrFail($callId);

        if ($call->teacher->user_id !== $teacherUserId) {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالانضمام.'], 403);
        }

        if ($call->status === 'ended') {
            return response()->json(['status' => false, 'message' => 'المكالمة منتهية.'], 400);
        }

        if ($call->status === 'initiated') {
            $call->update([
                'status'     => 'ongoing',
                'started_at' => now(),
            ]);
        }

        $token = $this->agoraService->generateToken($call->channel_name, $teacherUserId, 'publisher');

        return response()->json([
            'status' => true,
            'message' => 'تم الانضمام للمكالمة بنجاح.',
            'data'   => [
                'channel_name' => $call->channel_name,
                'agora_token'  => $token,
                'uid'          => $teacherUserId,
            ]
        ]);
    }
    public function endCall(Request $request, $callId)
    {
        $call = CallSession::findOrFail($callId);

        if ($call->status === 'ended') {
            return response()->json(['status' => true, 'message' => 'المكالمة منتهية مسبقاً.']);
        }

        $now = now();
        $durationMinutes = 0;

        if ($call->status === 'ongoing' && $call->started_at) {
            $durationSeconds = $call->started_at->diffInSeconds($now);
            $durationMinutes = (int) ceil($durationSeconds / 60);
        }

        DB::beginTransaction();
        try {
            $actualDeduction = 0;

            if ($durationMinutes > 0) {
                $activePackages = $this->getActivePackages($call->student_id, true);
                $totalAvailableMinutes = $activePackages->sum('remaining_minutes');
                $actualDeduction = min($durationMinutes, $totalAvailableMinutes);

                $minutesLeftToDeduct = $actualDeduction;
                foreach ($activePackages as $package) {
                    if ($minutesLeftToDeduct <= 0) break;

                    $deductFromThisPackage = min($package->remaining_minutes, $minutesLeftToDeduct);
                    $package->remaining_minutes -= $deductFromThisPackage;
                    $minutesLeftToDeduct -= $deductFromThisPackage;

                    if ($package->remaining_minutes <= 0) {
                        $package->remaining_minutes = 0;
                        $package->status = 'exhausted';
                    }
                    $package->save();
                }

                $teacher = Teacher::find($call->teacher_id);
                if ($teacher) {
                    $teacher->increment('minutes', $actualDeduction);
                }
            }

            $call->update([
                'ended_at'         => $now,
                'duration_minutes' => $actualDeduction,
                'status'           => 'ended'
            ]);

            DB::commit();

            $studentRemainingMinutes = $this->calculateTotalMinutes($call->student_id);

            return response()->json([
                'status'  => true,
                'message' => 'تم إنهاء المكالمة بنجاح.',
                'data'    => [
                    'call_duration_minutes'     => $actualDeduction,
                    'student_remaining_minutes' => (int) $studentRemainingMinutes
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('End Call Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء إنهاء المكالمة ومعالجة الرصيد.'
            ], 500);
        }
    }
}

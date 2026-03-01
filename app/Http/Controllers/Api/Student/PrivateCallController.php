<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Teacher;
use App\Models\UserPackage;
use App\Services\AgoraService;
use App\Events\IncomingPrivateCall; // ✅ استدعاء حدث الرنين
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrivateCallController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * 1. الطالب يبدأ المكالمة
     */
    public function startCall(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $user = auth()->user();

        // حساب الدقائق المتاحة من الباقات
        $totalAvailableMinutes = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->sum('remaining_minutes');

        if ($totalAvailableMinutes <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'ليس لديك رصيد دقائق كافٍ في باقاتك لإجراء المكالمة.'
            ], 400);
        }

        $teacher = Teacher::findOrFail($request->teacher_id);
        $channelName = 'private_call_' . $user->id . '_' . $teacher->id . '_' . time();

        $call = CallSession::create([
            'student_id'   => $user->id,
            'teacher_id'   => $teacher->id,
            'channel_name' => $channelName,
            'status'       => 'ongoing',
            'started_at'   => now(),
        ]);

        $token = $this->agoraService->generateToken($channelName, $user->id, 'publisher');

        // ==========================================
        // 🚀 إرسال إشارة الرنين إلى هاتف المعلم (Pusher)
        // ==========================================
        $callData = [
            'call_id'      => $call->id,
            'channel_name' => $channelName,
            'student_name' => $user->name,
        ];
        broadcast(new IncomingPrivateCall($teacher->id, $callData));

        return response()->json([
            'status' => true,
            'message' => 'تم بدء المكالمة وجاري الاتصال بالمعلم.',
            'data' => [
                'call_id'             => $call->id,
                'channel_name'        => $channelName,
                'agora_token'         => $token,
                'uid'                 => $user->id,
                'max_minutes_allowed' => (int) $totalAvailableMinutes
            ]
        ]);
    }

    /**
     * 2. المعلم يرد على المكالمة وينضم
     */
    public function joinCall(Request $request, $callId)
    {
        $teacherUserId = auth()->id();
        $call = CallSession::with('teacher')->findOrFail($callId);

        if ($call->teacher->user_id !== $teacherUserId) {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالانضمام لهذه المكالمة.'], 403);
        }

        if ($call->status === 'ended') {
            return response()->json(['status' => false, 'message' => 'المكالمة منتهية.'], 400);
        }

        $token = $this->agoraService->generateToken($call->channel_name, $teacherUserId, 'publisher');

        return response()->json([
            'status' => true,
            'data' => [
                'channel_name' => $call->channel_name,
                'agora_token'  => $token,
                'uid'          => $teacherUserId,
            ]
        ]);
    }

    /**
     * 3. إنهاء المكالمة وخصم الرصيد (يخصم من الباقات بالتدريج)
     */
    public function endCall(Request $request, $callId)
    {
        $call = CallSession::findOrFail($callId);

        if ($call->status === 'ended') {
            return response()->json(['status' => true, 'message' => 'المكالمة منتهية مسبقاً وتم خصم الرصيد.']);
        }

        $now = now();
        $durationSeconds = $call->started_at->diffInSeconds($now);
        $durationMinutes = (int) ceil($durationSeconds / 60);

        $activePackages = UserPackage::where('user_id', $call->student_id)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->where('remaining_minutes', '>', 0)
            ->orderBy('expires_at', 'asc')
            ->get();

        $totalAvailableMinutes = $activePackages->sum('remaining_minutes');
        $actualDeduction = min($durationMinutes, $totalAvailableMinutes);

        DB::beginTransaction();
        try {
            $call->update([
                'ended_at'         => $now,
                'duration_minutes' => $actualDeduction,
                'status'           => 'ended'
            ]);

            if ($actualDeduction > 0) {
                $minutesLeftToDeduct = $actualDeduction;

                foreach ($activePackages as $package) {
                    if ($minutesLeftToDeduct <= 0) break;

                    $deductFromThisPackage = min($package->remaining_minutes, $minutesLeftToDeduct);
                    $package->remaining_minutes -= $deductFromThisPackage;

                    if ($package->remaining_minutes == 0) {
                        $package->status = 'expired';
                    }

                    $package->save();
                    $minutesLeftToDeduct -= $deductFromThisPackage;
                }

                $teacher = Teacher::find($call->teacher_id);
                if ($teacher) {
                    $teacher->increment('minutes', $actualDeduction);
                }
            }

            DB::commit();

            $studentRemainingMinutes = UserPackage::where('user_id', $call->student_id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->sum('remaining_minutes');

            return response()->json([
                'status' => true,
                'message' => 'تم إنهاء المكالمة بنجاح.',
                'data' => [
                    'call_duration_minutes' => $actualDeduction,
                    'student_remaining_minutes' => (int) $studentRemainingMinutes
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('End Call Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء إنهاء المكالمة.'], 500);
        }
    }
}

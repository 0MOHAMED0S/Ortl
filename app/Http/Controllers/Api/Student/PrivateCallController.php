<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Teacher;
use App\Models\UserPackage;
use App\Services\AgoraService;
use App\Events\IncomingPrivateCall;
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
     * ==========================================
     * دوال مساعدة (Helpers) لضمان دقة الحساب في كل مكان
     * ==========================================
     */

    // جلب الباقات النشطة (يُستخدم للخصم)
    private function getActivePackages($userId, $lockForUpdate = false)
    {
        $query = UserPackage::where('user_id', $userId)
            ->whereIn('status', ['active', 'Active'])
            ->where('remaining_minutes', '>', 0)
            ->where(function ($q) {
                // الباقة صالحة إذا كان تاريخ الانتهاء في المستقبل، أو إذا لم يكن لها تاريخ (دائمة)
                $q->where('expires_at', '>', now())
                  ->orWhereNull('expires_at');
            })
            ->orderByRaw('expires_at IS NULL, expires_at ASC'); // الأقرب انتهاءً أولاً، والـ NULL (الدائمة) أخيراً

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    // حساب إجمالي الدقائق فقط
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

    /**
     * ==========================================
     * 1. الطالب يبدأ المكالمة
     * ==========================================
     */
    public function startCall(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $user = auth()->user();

        // 1️⃣ حساب إجمالي الدقائق المتاحة باحترافية
        $totalAvailableMinutes = $this->calculateTotalMinutes($user->id);

        if ($totalAvailableMinutes <= 0) {
            return response()->json([
                'status'  => false,
                'message' => 'ليس لديك رصيد دقائق كافٍ في باقاتك لإجراء المكالمة.'
            ], 400);
        }

        $teacher = Teacher::findOrFail($request->teacher_id);
        $channelName = 'private_call_' . $user->id . '_' . $teacher->id . '_' . time();

        // 2️⃣ إنشاء جلسة المكالمة
        $call = CallSession::create([
            'student_id'   => $user->id,
            'teacher_id'   => $teacher->id,
            'channel_name' => $channelName,
            'status'       => 'ongoing',
            'started_at'   => now(),
        ]);

        // 3️⃣ توليد توكن Agora
        $token = $this->agoraService->generateToken($channelName, $user->id, 'publisher');

        // 🚀 إرسال إشارة الرنين
        $callData = [
            'call_id'      => $call->id,
            'channel_name' => $channelName,
            'student_name' => $user->name,
        ];

        try {
            broadcast(new IncomingPrivateCall($teacher->id, $callData));
        } catch (\Exception $e) {
            Log::error('Pusher Broadcast Error: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم بدء المكالمة وجاري الاتصال بالمعلم.',
            'data'    => [
                'call_id'             => $call->id,
                'channel_name'        => $channelName,
                'agora_token'         => $token,
                'uid'                 => $user->id,
                'max_minutes_allowed' => (int) $totalAvailableMinutes
            ]
        ]);
    }

    /**
     * ==========================================
     * 2. المعلم يرد على المكالمة وينضم
     * ==========================================
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
            'data'   => [
                'channel_name' => $call->channel_name,
                'agora_token'  => $token,
                'uid'          => $teacherUserId,
            ]
        ]);
    }

    /**
     * ==========================================
     * 3. إنهاء المكالمة وخصم الرصيد
     * ==========================================
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

        DB::beginTransaction();
        try {
            // 1️⃣ جلب الباقات المتاحة بالترتيب الصحيح مع القفل المالي
            $activePackages = $this->getActivePackages($call->student_id, true);
            $totalAvailableMinutes = $activePackages->sum('remaining_minutes');

            // حماية: لا نخصم أكثر مما يملك
            $actualDeduction = min($durationMinutes, $totalAvailableMinutes);

            // 2️⃣ تحديث المكالمة
            $call->update([
                'ended_at'         => $now,
                'duration_minutes' => $actualDeduction,
                'status'           => 'ended'
            ]);

            // 3️⃣ الخصم التدريجي
            if ($actualDeduction > 0) {
                $minutesLeftToDeduct = $actualDeduction;

                foreach ($activePackages as $package) {
                    if ($minutesLeftToDeduct <= 0) break;

                    $deductFromThisPackage = min($package->remaining_minutes, $minutesLeftToDeduct);

                    $package->remaining_minutes -= $deductFromThisPackage;
                    $minutesLeftToDeduct -= $deductFromThisPackage;

                    if ($package->remaining_minutes <= 0) {
                        $package->remaining_minutes = 0;
                        $package->status = 'expired';
                    }

                    $package->save();
                }

                // 4️⃣ محفظة المعلم
                $teacher = Teacher::find($call->teacher_id);
                if ($teacher) {
                    $teacher->increment('minutes', $actualDeduction);
                }
            }

            DB::commit();

            // حساب الرصيد المتبقي النهائي لإرساله
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

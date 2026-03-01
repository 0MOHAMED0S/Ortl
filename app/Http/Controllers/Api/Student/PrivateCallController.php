<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Teacher;
use App\Models\UserPackage; // تم استدعاء مودل الباقات
use App\Services\AgoraService;
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

        // 1️⃣ حساب إجمالي الدقائق المتوفرة للطالب من جميع الباقات النشطة والصالحة
        $totalAvailableMinutes = UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now()) // التأكد أن الباقة لم تنتهِ صلاحيتها
            ->sum('remaining_minutes');

        if ($totalAvailableMinutes <= 0) {
            return response()->json([
                'status' => false,
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

        return response()->json([
            'status' => true,
            'message' => 'تم بدء المكالمة.',
            'data' => [
                'call_id'      => $call->id,
                'channel_name' => $channelName,
                'agora_token'  => $token,
                'uid'          => $user->id,
                // ✅ نرسل الحد الأقصى للدقائق للموبايل لكي يقوم بإنهاء المكالمة إجبارياً إذا انتهى الرصيد
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

        // 1️⃣ حساب مدة المكالمة وتقريبها لأقرب دقيقة أعلى (ceil)
        $durationSeconds = $call->started_at->diffInSeconds($now);
        $durationMinutes = (int) ceil($durationSeconds / 60);

        // 2️⃣ جلب باقات الطالب النشطة مرتبة حسب تاريخ الانتهاء (الأقرب انتهاءً تُستهلك أولاً)
        $activePackages = UserPackage::where('user_id', $call->student_id)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->where('remaining_minutes', '>', 0)
            ->orderBy('expires_at', 'asc')
            ->get();

        $totalAvailableMinutes = $activePackages->sum('remaining_minutes');

        // أقصى ما يمكن خصمه هو ما يملكه الطالب فعلياً (حماية)
        $actualDeduction = min($durationMinutes, $totalAvailableMinutes);

        DB::beginTransaction();
        try {
            // تحديث حالة المكالمة
            $call->update([
                'ended_at'         => $now,
                'duration_minutes' => $actualDeduction,
                'status'           => 'ended'
            ]);

            // 3️⃣ الخصم المتدرج من الباقات
            if ($actualDeduction > 0) {
                $minutesLeftToDeduct = $actualDeduction;

                foreach ($activePackages as $package) {
                    // إذا انتهينا من الخصم، نخرج من الحلقة
                    if ($minutesLeftToDeduct <= 0) {
                        break;
                    }

                    // نخصم المتاح في هذه الباقة أو ما تبقى من وقت المكالمة (أيهما أقل)
                    $deductFromThisPackage = min($package->remaining_minutes, $minutesLeftToDeduct);

                    $package->remaining_minutes -= $deductFromThisPackage;

                    // إذا نفدت الباقة بالكامل نغير حالتها
                    if ($package->remaining_minutes == 0) {
                        $package->status = 'expired'; // أو 'consumed' حسب ما تفضله في مشروعك
                    }

                    $package->save();

                    // تقليل المطلوب خصمه للفة القادمة
                    $minutesLeftToDeduct -= $deductFromThisPackage;
                }

                // 4️⃣ إضافة الدقائق لمحفظة المعلم
                $teacher = Teacher::find($call->teacher_id);
                if($teacher) {
                    $teacher->increment('minutes', $actualDeduction);
                }
            }

            DB::commit();

            // حساب الرصيد المتبقي الإجمالي للطالب لردّه للموبايل
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

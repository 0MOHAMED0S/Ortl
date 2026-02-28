<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherWalletController extends Controller
{
    /**
     * 1. جلب تفاصيل محفظة المعلم (الرصيد وآخر 10 طلبات سحب)
     */
    public function getWallet(Request $request)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
            }

            $totalMinutes   = $teacher->minutes ?? 0;
            $hourlyRate     = $teacher->salary ?? 0;
            $currentBalance = ($totalMinutes / 60) * $hourlyRate;

            // جلب آخر 5 طلبات للعرض السريع في المحفظة
            $recentWithdrawals = WithdrawalRequest::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($req) {
                    return [
                        'id'             => $req->id,
                        'amount'         => (float) $req->amount,
                        'account_number' => $req->account_number,
                        'status'         => $req->status,
                        'date'           => $req->created_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع بيانات المحفظة بنجاح.',
                'data'    => [
                    'wallet' => [
                        'total_minutes'   => (int) $totalMinutes,
                        'hourly_rate'     => (float) $hourlyRate,
                        'current_balance' => round($currentBalance, 2),
                        'currency'        => 'USD',
                    ],
                    'recent_withdrawals'  => $recentWithdrawals
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get Teacher Wallet Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ تقني: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2. جلب جميع طلبات السحب (سجل العمليات بالكامل) مع Pagination
     */
    public function getAllRequests(Request $request)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
            }

            // تحديد عدد العناصر في الصفحة، الافتراضي 15
            $perPage = $request->query('per_page', 15);

            $requests = WithdrawalRequest::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // تنسيق البيانات
            $requests->getCollection()->transform(function ($req) {
                return [
                    'id'             => $req->id,
                    'amount'         => (float) $req->amount,
                    'account_number' => $req->account_number,
                    'notes'          => $req->notes,
                    'status'         => $req->status,
                    'created_at'     => $req->created_at->format('Y-m-d H:i A'),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب سجل السحوبات بنجاح.',
                'data'    => $requests
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get All Withdrawals Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب السجل.'], 500);
        }
    }

    /**
     * 3. طلب سحب رصيد مالي
     */
    public function requestWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'account_number' => 'required|string|min:5|max:50',
            'notes'          => 'nullable|string|max:500',
        ], [
            'amount.required'         => 'يرجى إدخال المبلغ المراد سحبه بالدولار.',
            'amount.numeric'          => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'              => 'المبلغ يجب أن يكون أكبر من الصفر.',
            'account_number.required' => 'يرجى إدخال رقم الكاش أو الحساب البنكي.',
            'account_number.min'      => 'رقم الحساب قصير جداً.',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
        }

        $totalMinutes   = $teacher->minutes ?? 0;
        $hourlyRate     = $teacher->salary ?? 0;

        // منع القسمة على صفر في حالة لم يتم تحديد راتب للمعلم
        if ($hourlyRate <= 0) {
            return response()->json(['status' => false, 'message' => 'لم يتم تحديد سعر الساعة الخاص بك بعد.'], 400);
        }

        $currentBalance = ($totalMinutes / 60) * $hourlyRate;

        if (round($currentBalance, 2) < $validated['amount']) {
            return response()->json([
                'status'  => false,
                'message' => 'رصيدك الحالي لا يكفي لإتمام عملية السحب.',
                'current_balance' => round($currentBalance, 2)
            ], 400);
        }

        DB::beginTransaction();
        try {
            // تحويل المبلغ المسحوب إلى دقائق لخصمها
            $minutesToDeduct = ($validated['amount'] / $hourlyRate) * 60;

            // خصم الدقائق من رصيد المعلم
            $teacher->decrement('minutes', $minutesToDeduct);

            // إنشاء طلب السحب وحفظ عدد الدقائق المخصومة (سيفيدنا جداً في الإلغاء)
            $withdrawal = WithdrawalRequest::create([
                'teacher_id'     => $teacher->id,
                'amount'         => $validated['amount'],
                'account_number' => $validated['account_number'],
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'pending',
                // من الجيد إضافة عمود `deducted_minutes` في الداتابيز مستقبلاً إن أردت دقة 100%
            ]);

            DB::commit();

            $remainingMinutes = $teacher->fresh()->minutes;
            $remainingBalance = ($remainingMinutes / 60) * $hourlyRate;

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال طلب السحب بنجاح وجاري مراجعته.',
                'data'    => [
                    'request_id'        => $withdrawal->id,
                    'withdrawn_amount'  => $withdrawal->amount,
                    'remaining_balance' => round($remainingBalance, 2),
                    'currency'          => 'USD'
                ]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Withdrawal Request Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. إلغاء طلب السحب (إذا كان معلقاً فقط) وإرجاع الرصيد
     */
    public function cancelRequest($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
        }

        // جلب الطلب والتأكد أنه يخص هذا المعلم بالذات
        $withdrawal = WithdrawalRequest::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$withdrawal) {
            return response()->json(['status' => false, 'message' => 'الطلب غير موجود أو لا تملك صلاحية الوصول إليه.'], 404);
        }

        // التأكد من أن حالة الطلب "قيد المراجعة" (Pending)
        if ($withdrawal->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'لا يمكن إلغاء الطلب لأن حالته الحالية: ' . $withdrawal->status], 400);
        }

        DB::beginTransaction();
        try {
            $hourlyRate = $teacher->salary ?? 0;

            // حساب عدد الدقائق التي يجب إرجاعها للمحفظة
            // الدقائق = (المبلغ المسحوب / سعر الساعة) * 60
            $minutesToRefund = ($withdrawal->amount / $hourlyRate) * 60;

            // إرجاع الدقائق لرصيد المعلم
            $teacher->increment('minutes', $minutesToRefund);

            // مسح الطلب من قاعدة البيانات (أو يمكنك تغيير حالته إلى 'cancelled' إذا كان لديك هذه الحالة)
            $withdrawal->delete();

            DB::commit();

            // حساب الرصيد الجديد بعد الإرجاع
            $newMinutes = $teacher->fresh()->minutes;
            $newBalance = ($newMinutes / 60) * $hourlyRate;

            return response()->json([
                'status'  => true,
                'message' => 'تم إلغاء الطلب وإرجاع الرصيد إلى محفظتك بنجاح.',
                'data'    => [
                    'current_balance' => round($newBalance, 2),
                    'currency'        => 'USD'
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cancel Withdrawal Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء محاولة إلغاء الطلب.'], 500);
        }
    }
}

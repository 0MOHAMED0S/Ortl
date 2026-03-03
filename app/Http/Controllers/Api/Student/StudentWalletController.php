<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use App\Models\CallSession;
use App\Models\SlotBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentWalletController extends Controller
{
public function getTransactions(Request $request)
    {
        $userId = auth()->id();

        // 1️⃣ جلب المكالمات المنتهية (خصم دقائق)
        $calls = CallSession::where('student_id', $userId)
            ->where('status', 'ended')
            ->select('id', 'duration_minutes as minutes', 'started_at as date', DB::raw("'usage' as type"))
            ->get();

        // 2️⃣ جلب الحجوزات الملغاة (استرجاع دقائق)
        $refunds = SlotBooking::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->select('id', 'deducted_minutes as minutes', 'updated_at as date', DB::raw("'refund' as type"))
            ->get();

        // 3️⃣ جلب عمليات شراء الباقات (إضافة دقائق)
        $additions = UserPackage::where('user_id', $userId)
            ->with('package:id,name,base_minutes,bonus_minutes')
            ->select('id', 'package_id', 'created_at as date', DB::raw("'purchase' as type"))
            ->get();

        // 4️⃣ دمج العمليات
        $transactions = collect();

        foreach ($calls as $call) {
            $parsedDate = Carbon::parse($call->date);
            $transactions->push([
                'id'        => $call->id,
                'title'     => 'استهلاك دقائق (مكالمة تعليمية)',
                'minutes'   => "-" . $call->minutes,
                'type'      => 'out', // خصم
                'date'      => $parsedDate->format('Y-m-d'), // التاريخ فقط
                'time'      => $parsedDate->format('h:i A'), // الوقت فقط (مثال: 05:30 PM)
                'datetime'  => $parsedDate->format('Y-m-d h:i A'), // مدمج
                'sort_date' => $parsedDate->timestamp, // حقل خفي للترتيب الدقيق
                'icon'      => 'call_made'
            ]);
        }

        foreach ($refunds as $refund) {
            $parsedDate = Carbon::parse($refund->date);
            $transactions->push([
                'id'        => $refund->id,
                'title'     => 'استرجاع دقائق (إلغاء حجز)',
                'minutes'   => "+" . $refund->minutes,
                'type'      => 'in', // إضافة/استرجاع
                'date'      => $parsedDate->format('Y-m-d'),
                'time'      => $parsedDate->format('h:i A'),
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon'      => 'settings_backup_restore'
            ]);
        }

        foreach ($additions as $addition) {
            $parsedDate = Carbon::parse($addition->date);
            $totalPackageMinutes = 0;
            if ($addition->package) {
                $totalPackageMinutes = ($addition->package->base_minutes ?? 0) + ($addition->package->bonus_minutes ?? 0);
            }

            $transactions->push([
                'id'        => $addition->id,
                'title'     => 'شراء باقة: ' . ($addition->package->name ?? 'باقة مخصصة'),
                'minutes'   => "+" . $totalPackageMinutes,
                'type'      => 'in', // إضافة
                'date'      => $parsedDate->format('Y-m-d'),
                'time'      => $parsedDate->format('h:i A'),
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon'      => 'shopping_cart'
            ]);
        }

        // 5️⃣ الترتيب باستخدام الحقل الخفي (sort_date) لضمان الدقة، ثم حذفه لتنظيف الـ Response
        $sortedTransactions = $transactions->sortByDesc('sort_date')->values()->map(function ($item) {
            unset($item['sort_date']); // إخفاء حقل الترتيب
            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'تم جلب سجل العمليات بنجاح.',
            'data' => $sortedTransactions
        ]);
    }
public function getWalletSummary()
    {
        $userId = auth()->id();
        $now = Carbon::now();

        // 1️⃣ جلب الباقات النشطة مع تفاصيلها لحساب (المتاح) و (الأصل)
        $activePackages = UserPackage::with('package:id,name,base_minutes,bonus_minutes')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'Active'])
            ->where(function ($q) use ($now) {
                $q->where('expires_at', '>', $now)
                  ->orWhereNull('expires_at');
            })
            ->get();

        // حساب الدقائق المتاحة حالياً
        $availableMinutes = $activePackages->sum('remaining_minutes');

        // حساب إجمالي الدقائق الأصلية لهذه الباقات النشطة (X من أصل Y)
        $totalOriginalMinutes = $activePackages->sum(function ($up) {
            $pkgTotal = ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
            // نستخدم max كحماية: إذا كانت الباقة الأصلية محذوفة، نعتبر الأصل هو الرصيد المتبقي على الأقل
            return max($pkgTotal, $up->remaining_minutes);
        });

        // 2️⃣ الدقائق منتهية الصلاحية
        $expiredMinutes = UserPackage::where('user_id', $userId)
            ->where(function ($q) use ($now) {
                $q->where('status', 'expired')
                  ->orWhere('expires_at', '<=', $now);
            })
            ->where('remaining_minutes', '>', 0)
            ->sum('remaining_minutes');

        // 3️⃣ إجمالي الدقائق المستخدمة (التعلم الفعلي)
        $totalUsedMinutes = CallSession::where('student_id', $userId)
            ->where('status', 'ended')
            ->sum('duration_minutes');

        // 4️⃣ حساب ساعات التعلم (Learning Hours)
        $learningHours = floor($totalUsedMinutes / 60);
        $learningMinutesRemaining = $totalUsedMinutes % 60;

        // 5️⃣ جلب قائمة الباقات الحالية (نستخدم نفس المتغير لتوفير أداء الداتا بيز)
        $myPackages = UserPackage::with('package:id,name,base_minutes,bonus_minutes')
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($up) use ($now) {
                // السعة الأصلية للباقة الواحدة
                $pkgTotal = ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
                $original = max($pkgTotal, $up->remaining_minutes);

                return [
                    'package_name'     => $up->package->name ?? 'باقة مخصصة',
                    'remaining'        => $up->remaining_minutes,
                    'total_original'   => $original, // 👈 السعة الأصلية
                    'text_format'      => "{$up->remaining_minutes} دقيقة من أصل {$original}", // 👈 النص الجاهز للموبايل
                    'is_expired'       => $up->expires_at ? Carbon::parse($up->expires_at)->isPast() : false,
                    'expiry_date'      => $up->expires_at ? $up->expires_at : 'لا تنتهي',
                    'status'           => $up->status,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'تم استرجاع بيانات المحفظة بنجاح.',
            'data' => [
                'summary' => [
                    'available_minutes'      => (int) $availableMinutes,
                    'total_original_minutes' => (int) $totalOriginalMinutes, // 👈 الرقم الأصلي الإجمالي
                    'balance_text'           => "الرصيد المتبقي {$availableMinutes} من أصل {$totalOriginalMinutes} دقيقة", // 👈 النص الجاهز للمحفظة
                    'used_minutes'           => (int) $totalUsedMinutes,
                    'expired_minutes'        => (int) $expiredMinutes,
                ],
                'learning_stats' => [
                    'total_hours'   => (int) $learningHours,
                    'total_minutes' => (int) $totalUsedMinutes,
                    'formatted'     => "{$learningHours} ساعة و {$learningMinutesRemaining} دقيقة",
                ],
                'packages_details' => $myPackages
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Models\GiftCard; // 🚀 تأكد من إضافة هذا الـ Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentWalletController extends Controller
{
public function getTransactions(Request $request)
    {
        $userId = auth()->id();

        // 1️⃣ استهلاك الدقائق (مكالمات منتهية)
        $calls = CallSession::where('student_id', $userId)
            ->where('status', 'ended')
            ->select('id', 'duration_minutes as minutes', 'started_at as date')
            ->get();

        // 2️⃣ استرجاع الدقائق (إلغاء حجز)
        $refunds = SlotBooking::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->where('deducted_minutes', '>', 0)
            ->select('id', 'deducted_minutes as minutes', 'updated_at as date')
            ->get();

        // 3️⃣ شراء الباقات (إضافة رصيد)
        $additions = UserPackage::where('user_id', $userId)
            ->with('package:id,name,base_minutes,bonus_minutes')
            ->select('id', 'package_id', 'created_at as date')
            ->get();

        // 4️⃣ الهدايا المستلمة (شحن رصيد من كود)
        $receivedGifts = GiftCard::where('claimed_by_user_id', $userId)
            ->where('status', 'claimed')
            ->with('sender:id,name')
            ->select('id', 'minutes', 'occasion', 'sender_id', 'claimed_at as date')
            ->get();

        // 5️⃣ الهدايا المرسلة (سجل مالي)
        $sentGifts = GiftCard::where('sender_id', $userId)
            ->where('payment_status', 'paid')
            ->select('id', 'minutes', 'recipient_name', 'recipient_email', 'occasion', 'created_at as date')
            ->get();

        $transactions = collect();

        // معالجة المكالمات
        foreach ($calls as $call) {
            $parsedDate = Carbon::parse($call->date);
            $transactions->push([
                'id'        => $call->id,
                'title'     => 'جلسة تعليمية مباشرة',
                'subtitle'  => 'استهلاك رصيد دقائق',
                'minutes'   => "-" . $call->minutes . " دقيقة",
                'type'      => 'out',
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon_type' => 'call'
            ]);
        }

        // معالجة المبالغ المستردة
        foreach ($refunds as $refund) {
            $parsedDate = Carbon::parse($refund->date);
            $transactions->push([
                'id'        => $refund->id,
                'title'     => 'مستردات حجز ملغي',
                'subtitle'  => 'تمت إعادة الدقائق لمحفظتك',
                'minutes'   => "+" . $refund->minutes . " دقيقة",
                'type'      => 'in',
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon_type' => 'refund'
            ]);
        }

        // معالجة شراء الباقات
        foreach ($additions as $addition) {
            $parsedDate = Carbon::parse($addition->date);
            $totalMins = ($addition->package->base_minutes ?? 0) + ($addition->package->bonus_minutes ?? 0);
            $transactions->push([
                'id'        => $addition->id,
                'title'     => 'شراء باقة: ' . ($addition->package->name ?? 'باقة دقائق'),
                'subtitle'  => 'عملية شحن رصيد ناجحة',
                'minutes'   => "+" . $totalMins . " دقيقة",
                'type'      => 'in',
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon_type' => 'purchase'
            ]);
        }

        // معالجة الهدايا المستلمة
        foreach ($receivedGifts as $gift) {
            $parsedDate = Carbon::parse($gift->date ?? now());
            $transactions->push([
                'id'        => $gift->id,
                'title'     => 'هدية مستلمة',
                'subtitle'  => 'من: ' . ($gift->sender->name ?? 'فاعل خير'),
                'minutes'   => "+" . $gift->minutes . " دقيقة",
                'type'      => 'in',
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon_type' => 'gift_received'
            ]);
        }

        // معالجة الهدايا المرسلة
        foreach ($sentGifts as $gift) {
            $parsedDate = Carbon::parse($gift->date);
            $transactions->push([
                'id'        => $gift->id,
                'title'     => 'إرسال بطاقة هدية',
                'subtitle'  => 'إلى: ' . ($gift->recipient_name ?? $gift->recipient_email),
                'minutes'   => $gift->minutes . " دقيقة",
                'type'      => 'neutral',
                'datetime'  => $parsedDate->format('Y-m-d h:i A'),
                'sort_date' => $parsedDate->timestamp,
                'icon_type' => 'gift_sent'
            ]);
        }

        // الترتيب من الأحدث للأقدم
        $sortedData = $transactions->sortByDesc('sort_date')->values()->map(function ($item) {
            unset($item['sort_date']);
            return $item;
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب سجل العمليات بنجاح.',
            'data'    => $sortedData
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
        // 5️⃣ جلب قائمة الباقات الحالية
        $myPackages = UserPackage::with('package:id,name,base_minutes,bonus_minutes')
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($up) use ($now) {
                $pkgTotal = ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
                $original = max($pkgTotal, $up->remaining_minutes);
                return [
                    'package_name'     => $up->package->name ?? 'باقة مخصصة',
                    'remaining'        => $up->remaining_minutes,
                    'total_original'   => $original,
                    'text_format'      => "{$up->remaining_minutes} دقيقة من أصل {$original}",
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
                    'total_original_minutes' => (int) $totalOriginalMinutes,
                    'balance_text'           => "الرصيد المتبقي {$availableMinutes} من أصل {$totalOriginalMinutes} دقيقة",
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

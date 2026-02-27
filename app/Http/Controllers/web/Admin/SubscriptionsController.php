<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SubscriptionsController extends Controller
{
public function index(Request $request)
{
    try {
        // 1. بناء الاستعلام مع العلاقات المتداخلة (Eager Loading)
        $query = Order::with(['package', 'coupon', 'user.student.country'])
            ->latest();

        // 2. تطبيق فلاتر البحث والحالة
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('transaction_id', 'like', "%{$search}%")
                ->orWhere('paymob_order_id', 'like', "%{$search}%");
            });
        }

        // 3. الفلترة الزمنية
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // 4. حسابات الإحصائيات (للعمليات الناجحة فقط)
        $paidOrders = Order::where('status', 'paid')
            ->with(['user.student.country', 'package'])
            ->get();

        $financials = $paidOrders->map(function ($order) {
            $country = $order->user->student->country ?? null;
            $rate = ($country && $country->rate_to_usd > 0) ? $country->rate_to_usd : 1;

            $priceBeforeUSD = $order->package->price ?? 0;
            $priceAfterUSD = ($order->currency == 'USD') ? $order->amount : ($order->amount / $rate);

            return [
                'before' => $priceBeforeUSD,
                'after'  => $priceAfterUSD,
                'discount' => max(0, $priceBeforeUSD - $priceAfterUSD),
            ];
        });

        $stats = [
            'net_revenue_usd'     => $financials->sum('after'),
            'total_discounts_usd' => $financials->sum('discount'),
            'success_orders'      => $paidOrders->count(),
            'pending_orders'      => Order::where('status', 'pending')->count(),
        ];

        // 5. جلب البيانات مع الترقيم
        $orders = $query->paginate(15)->withQueryString();

        // 6. تحويل البيانات للعرض
        $orders->getCollection()->transform(function ($order) {
            $student = $order->user->student ?? null;
            $country = $student->country ?? null;
            $package = $order->package ?? null;
            $rate = ($country && $country->rate_to_usd > 0) ? $country->rate_to_usd : 1;

            $beforeUSD = $package->price ?? 0;
            $afterUSD = ($order->currency == 'USD') ? $order->amount : ($order->amount / $rate);

            $isNew = $order->created_at->gt(Carbon::now()->subHours(24));

            // --- منطق الصورة الصحيح ---
            // نتحقق من وجود المسار في قاعدة البيانات وفي نظام الملفات
            if ($student && $student->profile_photo_path && Storage::disk('public')->exists($student->profile_photo_path)) {
                $userImage = asset('storage/' . $student->profile_photo_path);
            } else {
                // الافتراضي في حال عدم وجود صورة
                $userImage = "https://ui-avatars.com/api/?name=" . urlencode($order->user->name ?? 'U') . "&background=1b4d3e&color=fff";
            }

            return (object)[
                'id'                  => $order->user_id,
                'transaction_id'      => $order->transaction_id ?? 'N/A',
                'paymob_id'           => $order->paymob_order_id,
                'user'                => (object)[
                    'name'  => $order->user->name ?? 'مستخدم محذوف',
                    'email' => $order->user->email ?? '-',
                    'phone' => $student->phone ?? 'غير مسجل',
                    'image' => $userImage, // الصورة المصححة هنا
                ],
                'country'             => (object)[
                    'name'          => $country->name ?? 'غير محدد',
                    'currency_code' => $order->currency,
                    'rate'          => $rate,
                ],
                'package_name'          => $package->name ?? 'باقة غير معروفة',
                'package_base_minutes'  => $package->base_minutes ?? 0,
                'package_bonus_minutes' => $package->bonus_minutes ?? 0,
                'package_validity_days' => $package->validity_days ?? 0,
                'package_description'   => $package->description ?? 'لا يوجد وصف متاح.',
                'package_price_usd'     => number_format($beforeUSD, 2),

                'price_before_usd'    => number_format($beforeUSD, 2),
                'price_after_usd'     => number_format($afterUSD, 2),
                'discount_amount_usd' => number_format(max(0, $beforeUSD - $afterUSD), 2),
                'amount_local'        => number_format($order->amount, 2),
                'currency'            => $order->currency,
                'status'              => $order->status,
                'is_new'              => $isNew,
                'coupon'              => $order->coupon ? (object)['code' => $order->coupon->code, 'percent' => $order->coupon->percent] : null,
                'date_human'          => $order->created_at->format('Y/m/d H:i'),
            ];
        });

        return view('dashboard.subscriptions', compact('orders', 'stats'));

    } catch (\Throwable $e) {
        Log::error('Subscriptions Controller Error: ' . $e->getMessage());
        return back()->with('error', 'حدث خطأ أثناء جلب البيانات: ' . $e->getMessage());
    }
}
}

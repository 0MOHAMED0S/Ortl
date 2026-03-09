<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Services\PayTabsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StudentBuyPackageController extends Controller
{
    // 1️⃣ Start Payment
    public function buyPackage(Request $request, PayTabsService $payTabsService)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'coupon'     => 'nullable|string'
        ]);

        try {
            $user = auth()->user();
            $package = Package::findOrFail($request->package_id);

            // 1. التأكد من أن الطالب ليس لديه باقة نشطة من نفس النوع
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل.'], 400);
            }

            // 2. تحديد العملة والسعر الأساسي (مطابق لدالة getPrice)
            $country = $user->country;
            $rate = ($country && $country->rate_to_usd > 0) ? (float) $country->rate_to_usd : 1;
            $currency = ($country && $country->currency_code) ? $country->currency_code : config('paytabs.currency', 'USD');

            // سعر الباقة النهائي بالدولار (المخزن في قاعدة البيانات)
            $packageFinalPriceUsd = (float) $package->price;

            // تحويل السعر للعملة المحلية
            $convertedPrice = $packageFinalPriceUsd * $rate;

            // 3. حساب الخصم الإضافي (الكوبون)
            $discountAmount = 0;
            $couponId = null;
            $couponCode = $request->input('coupon');

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)
                    ->where('status', 'active')
                    ->first();

                if (!$coupon || ($coupon->expiry_date && $coupon->expiry_date->isPast()) || ($coupon->used >= $coupon->limit)) {
                    return response()->json(['error' => 'كود الخصم غير صحيح أو منتهي الصلاحية.'], 422);
                }

                $couponId = $coupon->id;
                $discountAmount = ($convertedPrice * $coupon->percent) / 100;
            }

            // 4. السعر النهائي المطلوب دفعه (يجب تقريبه لمنزلتين لتجنب أخطاء PayTabs)
            $finalPriceToPay = max($convertedPrice - $discountAmount, 0);
            $finalPriceToPay = round($finalPriceToPay, 2);

            // 5. التعامل مع الباقة المجانية (إذا كان السعر 0 بعد الخصم 100%)
            if ($finalPriceToPay <= 0) {
                return DB::transaction(function () use ($user, $package, $couponId) {
                    UserPackage::create([
                        'user_id'           => $user->id,
                        'package_id'        => $package->id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at'        => now()->addDays($package->validity_days),
                        'status'            => 'active'
                    ]);

                    if ($couponId) {
                        Coupon::where('id', $couponId)->increment('used');
                    }

                    return response()->json([
                        'message'      => 'تم تفعيل الباقة بنجاح مجاناً باستخدام الكوبون.',
                        'redirect_url' => route('payment.success') // رابط النجاح في الفرونت إند
                    ]);
                });
            }

            // 6. إنشاء طلب الدفع (Pending Order)
            $order = Order::create([
                'user_id'    => $user->id,
                'package_id' => $package->id,
                'coupon_id'  => $couponId,
                'country_id' => optional($country)->id,
                'amount'     => $finalPriceToPay,
                'currency'   => strtoupper($currency),
                'status'     => 'pending',
            ]);

            // 7. إرسال الطلب إلى PayTabs
            $payment = $payTabsService->createPayment($order, $user);

            if (isset($payment['redirect_url'])) {
                return response()->json([
                    'payment_url' => $payment['redirect_url'],
                    'order_id'    => $order->id,
                    'data'        => $payment
                ]);
            }

            return response()->json([
                'error' => 'فشل في إنشاء طلب الدفع عبر بوابات الدفع',
                'debug' => $payment
            ], 400);

        } catch (\Throwable $e) {
            Log::error('PayTabs Purchase Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء معالجة الطلب.'], 500);
        }
    }

    // 2️⃣ PayTabs Server Callback (IMPORTANT)
// 2️⃣ PayTabs Server Callback (IMPORTANT)
    public function handleCallback(Request $request)
    {
        $payload = $request->all();

        // Mapping Cart ID and Status for both "Flat" and "Nested" PayTabs formats
        $orderId = $payload['cartId'] ?? $payload['cart_id'] ?? null;
        $status  = $payload['respStatus'] ?? $payload['payment_result']['response_status'] ?? null;

        if ($status === 'A' && $orderId) {
            // جلب الطلب مع بيانات الطالب والباقة لتمريرها في الإشعار
            $order = Order::with(['user', 'package'])->find($orderId);

            // Only process if the order exists and isn't already paid (prevents duplicate processing)
            if ($order && $order->status !== 'paid') {

                DB::transaction(function () use ($order, $payload) {
                    // A. Update the Order Status
                    $order->update([
                        'status' => 'paid',
                        'transaction_id' => $payload['tranRef'] ?? $payload['tran_ref'] ?? null
                    ]);

                    // B. Professional Coupon Update
                    if ($order->coupon_id) {
                        $coupon = Coupon::find($order->coupon_id);
                        if ($coupon) {
                            $coupon->increment('used');
                            Log::info("Coupon ID {$coupon->id} usage incremented for Order #{$order->id}");
                        }
                    }

                    // C. Activate the User's Package
                    $package = $order->package;
                    UserPackage::create([
                        'user_id'           => $order->user_id,
                        'package_id'        => $order->package_id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at'        => now()->addDays($package->validity_days),
                        'status'            => 'active'
                    ]);

                    Log::info("Order #{$order->id} fulfilled successfully.");
                });

                // ==========================================
                // 🔔 إرسال إشعار لحظي لمديري النظام (Admins) بعد نجاح الدفع
                // ==========================================
                try {
                    $admins = \App\Models\User::where('role', 'admin')->get();

                    if ($admins->count() > 0) {
                        $studentName = $order->user->name ?? 'طالب';
                        $packageName = $order->package->name ?? 'باقة';

                        $notificationData = [
                            'order_id'     => $order->id,
                            'student_name' => $studentName,
                            'package_name' => $packageName,
                            'amount'       => $order->amount,
                            'currency'     => $order->currency,
                        ];

                        // 1. الحفظ في الداتابيز لكل المديرين
                        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                            'عملية شراء جديدة 💰',
                            "قام الطالب {$studentName} بشراء {$packageName} بنجاح.",
                            'new_order',
                            $notificationData
                        ));

                        // 2. إرسال البث اللحظي (Pusher) لكل مدير
                        foreach ($admins as $admin) {
                            broadcast(new \App\Events\NewOrderPaid($admin->id, $notificationData));
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Admin Order Notification Error: ' . $e->getMessage());
                }
                // ==========================================
            }
        }

        // Always return 200 OK to PayTabs so they stop retrying the webhook
        return response('OK');
    }

    // 3️⃣ Frontend Redirect
    public function handleResponse(Request $request)
    {
        $data = $request->all();

        // Mapping the keys from your specific PayTabs response
        $status  = $data['respStatus'] ?? null;
        $cartId  = $data['cartId'] ?? null;
        $tranRef = $data['tranRef'] ?? null;

        if ($status === 'A') {
            return view('payments.success');
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Payment failed or cancelled',
            'received_status' => $status
        ], 400);
    }
}

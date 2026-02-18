<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyPackageController extends Controller
{
    public function buy(Request $request, Package $package, PaymobService $paymob)
    {
        try {
            $user = auth()->user();

            // 0. التحقق من عدم وجود باقة نشطة
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل.'], 400);
            }

            // 1. تحديد السعر الأساسي والعملة
            $country = $user->isStudent() ? $user->studentProfile->country : ($user->isTeacher() ? $user->teacherProfile->country : null);

            if ($country && !empty($country->paymob_integration_id)) {
                $integrationId = $country->paymob_integration_id;
                $currency = $country->currency_code;
                $rate = ($country->rate_to_usd > 0) ? $country->rate_to_usd : 1;
                $price = $package->price * $rate;
            } else {
                $integrationId = env('PAYMOB_INTEGRATION_ID');
                $currency = env('PAYMOB_DEFAULT_CURRENCY', 'EGP');
                $price = $package->price;
            }

            // --- منطق كود الخصم (Coupon Logic) ---
            $discountAmount = 0;
            $couponId = null;
            $couponCode = $request->input('coupon');

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->where('status', 'active')->first();

                // التحقق من صحة الكود وصلاحيته
                if (!$coupon || ($coupon->expiry_date && $coupon->expiry_date->isPast()) || ($coupon->used >= $coupon->limit)) {
                    return response()->json(['error' => 'كود الخصم غير صحيح أو منتهي الصلاحية.'], 422);
                }

                $couponId = $coupon->id;
                $discountAmount = ($price * $coupon->percent) / 100;
            }

            $finalPrice = (int) round(max($price - $discountAmount, 0));

            // --- التعامل مع السعر صفر (باقة مجانية أو خصم 100%) ---
            if ($finalPrice <= 0) {
                return DB::transaction(function () use ($user, $package, $couponId) {
                    UserPackage::create([
                        'user_id' => $user->id,
                        'package_id' => $package->id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at' => now()->addDays($package->validity_days),
                        'status' => 'active'
                    ]);

                    // تحديث عدد مرات استخدام الكود فوراً لأنها عملية ناجحة مجانية
                    if ($couponId) {
                        Coupon::where('id', $couponId)->increment('used');
                    }

                    return response()->json([
                        'message' => 'تم تفعيل الباقة بنجاح',
                        'redirect_url' => route('payment.success')
                    ]);
                });
            }

            // 2. طلب الدفع من Paymob
            $token = $paymob->authenticate();
            $paymobOrderResponse = $paymob->createOrder($token, $finalPrice, $currency);

            if (!$paymobOrderResponse->successful() || !isset($paymobOrderResponse['id'])) {
                return response()->json(['error' => 'فشل في إنشاء طلب الدفع'], 400);
            }

            $paymobOrderId = $paymobOrderResponse['id'];

            // حفظ الطلب مع ربط الكود (coupon_id)
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'coupon_id' => $couponId, // مهم جداً للمتابعة في الـ Callback
                'country_id' => $country->id ?? null,
                'amount' => $finalPrice,
                'currency' => $currency,
                'paymob_order_id' => $paymobOrderId,
                'status' => 'pending'
            ]);

            $paymentToken = $paymob->generatePaymentKey($token, $paymobOrderId, $finalPrice, $user, $currency, $integrationId);
            $iframeUrl = $paymob->getIframeUrl(env('PAYMOB_IFRAME_ID'), $paymentToken);

            return response()->json([
                'iframe_url' => $iframeUrl,
                'order_id' => $order->id
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع'], 500);
        }
    }

    public function handleCallback(Request $request, PaymobService $paymob)
    {
        $data = $request->all();

        // (اختياري) تفعيل HMAC هنا لزيادة الأمان

        $isSuccess = ($data['success'] === "true" || $data['success'] === true);
        $paymobOrderId = $data['order'];

        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        if (!$order || $order->status === 'paid') {
            return redirect()->route('student.dashboard');
        }

        if ($isSuccess) {
            DB::transaction(function () use ($order, $data) {
                // 1. تحديث حالة الطلب
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => $data['id']
                ]);

                // 2. زيادة عداد استخدام الكود إذا وُجد
                if ($order->coupon_id) {
                    Coupon::where('id', $order->coupon_id)->increment('used');
                }

                // 3. تفعيل الباقة
                $package = $order->package;
                UserPackage::create([
                    'user_id' => $order->user_id,
                    'package_id' => $package->id,
                    'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'expires_at' => now()->addDays($package->validity_days),
                    'status' => 'active'
                ]);
            });

            return redirect()->route('payment.success');
        }

        $order->update(['status' => 'failed']);
        return redirect()->route('packages.index')->with('error', 'فشلت عملية الدفع.');
    }
}

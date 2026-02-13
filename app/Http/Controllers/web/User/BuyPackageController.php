<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\package;
use App\Models\UserPackage;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyPackageController extends Controller
{
    public function buy(Request $request, package $package, PaymobService $paymob)
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

            // 1. تحديد السعر (تأكد من أن rate_to_usd ليس 0 في قاعدة البيانات)
            $country = $user->isStudent() ? $user->studentProfile->country : ($user->isTeacher() ? $user->teacherProfile->country : null);

            if ($country && !empty($country->paymob_integration_id)) {
                $integrationId = $country->paymob_integration_id;
                $currency = $country->currency_code;
                // تأكد أن rate_to_usd لا يساوي 0، إذا كان فارغاً نستخدم 1
                $rate = ($country->rate_to_usd > 0) ? $country->rate_to_usd : 1;
                $price = $package->price * $rate;
            } else {
                $integrationId = env('PAYMOB_INTEGRATION_ID');
                $currency = env('PAYMOB_DEFAULT_CURRENCY', 'EGP');
                $price = $package->price;
            }

            $finalPrice = (int) round($price);

            // --- التعامل مع السعر صفر (باقة مجانية) ---
            if ($finalPrice <= 0) {
                return DB::transaction(function () use ($user, $package) {
                    // إنشاء سجل باقة للمستخدم فوراً
                    UserPackage::create([
                        'user_id' => $user->id,
                        'package_id' => $package->id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at' => now()->addDays($package->validity_days),
                        'status' => 'active'
                    ]);

                    return response()->json([
                        'message' => 'تم تفعيل الباقة المجانية بنجاح',
                        'redirect_url' => route('payment.success')
                    ]);
                });
            }

            // 2. إذا كان السعر أكبر من صفر، نذهب لـ Paymob
            $token = $paymob->authenticate();
            $paymobOrderResponse = $paymob->createOrder($token, $finalPrice, $currency);

            if (!$paymobOrderResponse->successful() || !isset($paymobOrderResponse['id'])) {
                return response()->json([
                    'error' => 'فشل في إنشاء طلب الدفع',
                    'details' => $paymobOrderResponse->json()
                ], 400);
            }

            $paymobOrderId = $paymobOrderResponse['id'];

            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
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

        // 1. التحقق من صحة البيانات (HMAC)
        // if (!$paymob->validateHmac($data)) {
        //     Log::alert('Security Alert: Invalid Paymob HMAC', ['data' => $data]);
        //     return abort(403, 'Unauthorized');
        // }

        $isSuccess = $data['success'] === "true";
        $paymobOrderId = $data['order'];

        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        if (!$order || $order->status === 'paid') {
            return redirect()->route('student.dashboard');
        }

        if ($isSuccess) {
            DB::transaction(function () use ($order, $data) {
                // تحديث حالة الطلب
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => $data['id']
                ]);
                if ($order->coupon_id) {
                    Coupon::where('id', $order->coupon_id)->increment('used');
                }
                // تفعيل الباقة للمستخدم
                $package = $order->package;
                $totalMinutes = $package->base_minutes + ($package->bonus_minutes ?? 0);

                UserPackage::create([
                    'user_id' => $order->user_id,
                    'package_id' => $package->id,
                    'remaining_minutes' => $totalMinutes,
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

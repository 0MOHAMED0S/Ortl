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

            // --- الخطوة 0: التحقق من عدم وجود باقة نشطة من نفس النوع ---
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل، لا يمكنك شراء نفس الباقة مرتين.'], 400);
            }

            // 1. تحديد الدولة والعملة
            $country = $user->isStudent() ? $user->studentProfile->country : ($user->isTeacher() ? $user->teacherProfile->country : null);

            if ($country && !empty($country->paymob_integration_id)) {
                $integrationId = $country->paymob_integration_id;
                $currency = $country->currency_code;
                $price = $package->price * ($country->rate_to_usd ?: 1);
            } else {
                $integrationId = env('PAYMOB_INTEGRATION_ID');
                $currency = env('PAYMOB_DEFAULT_CURRENCY', 'EGP');
                $price = $country ? ($package->price * $country->rate_to_usd) : $package->price;
            }

            // 2. تطبيق الخصم (إذا وُجد كوبون)
            $couponId = null;
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', 'active')
                    ->where('expiry_date', '>=', now()->toDateString())
                    ->first();

                if ($coupon && ($coupon->limit == 0 || $coupon->used < $coupon->limit)) {
                    $price -= ($price * $coupon->percent) / 100;
                    $couponId = $coupon->id;
                }
            }

            $finalPrice = (int) round($price);

            // 3. الاتصال بـ Paymob
            $token = $paymob->authenticate();
            $paymobOrderResponse = $paymob->createOrder($token, $finalPrice, $currency);

            // تصحيح: التحقق من نجاح الطلب قبل الوصول للـ ID
            if (!$paymobOrderResponse->successful() || !isset($paymobOrderResponse['id'])) {
                Log::error('Paymob Order Error', $paymobOrderResponse->json());
                throw new \Exception("فشل إنشاء الطلب في باي موب: " . ($paymobOrderResponse['message'] ?? 'خطأ غير معروف'));
            }

            $paymobOrderId = $paymobOrderResponse['id'];

            // 4. إنشاء الطلب محلياً
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                // 'coupon_id' => Null,
                'country_id' => $country->id ?? null,
                'amount' => $finalPrice,
                'currency' => $currency,
                'paymob_order_id' => $paymobOrderId,
                'status' => 'pending'
            ]);

            // 5. توليد مفتاح الدفع
            $paymentToken = $paymob->generatePaymentKey($token, $paymobOrderId, $finalPrice, $user, $currency, $integrationId);
            $iframeUrl = $paymob->getIframeUrl(env('PAYMOB_IFRAME_ID'), $paymentToken);

            return response()->json([
                'iframe_url' => $iframeUrl,
                'order_id' => $order->id
            ]);

        } catch (\Throwable $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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

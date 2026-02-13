<?php
namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
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

            // --- إضافة التحقق (Validation) ---
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json([
                    'error' => 'لديك باقة نشطة بالفعل من هذا النوع، لا يمكنك التجديد حتى تنتهي الباقة الحالية.'
                ], 400);
            }
            // ---------------------------------

            // 1. تحديد السعر بناءً على الدولة
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

            // 2. تطبيق الخصم (Percentage Only)
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

            // 3. إجراءات Paymob
            $token = $paymob->authenticate();
            $paymobOrder = $paymob->createOrder($token, $finalPrice, $currency);

            if (!isset($paymobOrder['id'])) {
                throw new \Exception("Paymob Order creation failed.");
            }

            // 4. إنشاء الطلب المحلي
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'coupon_id' => $couponId,
                'country_id' => $country->id ?? null,
                'amount' => $finalPrice,
                'currency' => $currency,
                'paymob_order_id' => $paymobOrder['id'],
                'status' => 'pending'
            ]);

            $paymentToken = $paymob->generatePaymentKey($token, $paymobOrder['id'], $finalPrice, $user, $currency, $integrationId);
            $iframeUrl = $paymob->getIframeUrl(env('PAYMOB_IFRAME_ID'), $paymentToken);

            return response()->json([
                'iframe_url' => $iframeUrl,
                'order_id' => $order->id,
                'amount' => $finalPrice
            ]);

        } catch (\Throwable $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء تجهيز عملية الدفع.'], 500);
        }
    }

    public function handleCallback(Request $request, PaymobService $paymob)
    {
        $data = $request->all();
        $isSuccess = ($data['success'] === "true" || $data['success'] === true);
        $order = Order::where('paymob_order_id', $data['order'])->first();

        if (!$order || $order->status === 'paid') {
            return redirect()->route('student.dashboard');
        }

        if ($isSuccess) {
            DB::transaction(function () use ($order, $data) {
                $order->update(['status' => 'paid', 'transaction_id' => $data['id']]);

                if ($order->coupon_id) {
                    Coupon::where('id', $order->coupon_id)->increment('used');
                }

                UserPackage::create([
                    'user_id' => $order->user_id,
                    'package_id' => $order->package_id,
                    'remaining_minutes' => $order->package->base_minutes + ($order->package->bonus_minutes ?? 0),
                    'expires_at' => now()->addDays($order->package->validity_days),
                    'status' => 'active'
                ]);
            });
            return redirect()->route('payment.success');
        }

        $order->update(['status' => 'failed']);
        return redirect()->route('packages.index')->with('error', 'فشلت عملية الدفع.');
    }
}

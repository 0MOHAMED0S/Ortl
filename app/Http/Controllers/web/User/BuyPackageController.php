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
    public function buy(package $package, PaymobService $paymob)
    {
        try {
            $user = auth()->user();

            // 1. Fetch Country from Profile
            $country = null;
            if ($user->isStudent()) {
                $country = $user->studentProfile->country ?? null;
            } elseif ($user->isTeacher()) {
                $country = $user->teacherProfile->country ?? null;
            }

            // 2. Logic for Integration and Currency
            if ($country && !empty($country->paymob_integration_id)) {
                $integrationId = $country->paymob_integration_id;
                $currency = $country->currency_code;

                // Calculate local price and force to Integer
                $price = (int) round($package->price * ($country->rate_to_usd ?: 1));
            } else {
                $integrationId = env('PAYMOB_INTEGRATION_ID');
                $currency = env('PAYMOB_DEFAULT_CURRENCY', 'EGP');

                // Convert to default currency price and force to Integer
                $price = $country
                    ? (int) round($package->price * $country->rate_to_usd)
                    : (int) $package->price;
            }

            // 3. Paymob Authentication
            $token = $paymob->authenticate();

            // 4. Create Paymob Order
            $paymobOrder = $paymob->createOrder($token, $price, $currency);

            if (!isset($paymobOrder['id'])) {
                throw new \Exception("Paymob Order creation failed.");
            }

            // 5. Create Local Order (Price is now a clean integer)
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'country_id' => $country->id ?? null,
                'amount' => $price,
                'currency' => $currency,
                'paymob_order_id' => $paymobOrder['id'],
                'status' => 'pending'
            ]);

            // 6. Generate Payment Key
            $paymentToken = $paymob->generatePaymentKey(
                $token,
                $paymobOrder['id'],
                $price,
                $user,
                $currency,
                $integrationId
            );

            $iframeUrl = $paymob->getIframeUrl(env('PAYMOB_IFRAME_ID'), $paymentToken);

            return response()->json([
                'iframe_url' => $iframeUrl,
                'order_id' => $order->id
            ]);
        } catch (\Throwable $e) {
            Log::error('Paymob Integer Price Error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment initialization failed.'], 500);
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

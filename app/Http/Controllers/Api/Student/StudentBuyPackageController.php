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

            // 1. Check for an already active package
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل.'], 400);
            }

            // 2. Determine Currency and Price (Regional Logic)
            // Note: We use $user->country relationship assuming it exists
            $country = $user->country;

            if ($country && $country->currency_code) {
                $currency = $country->currency_code;
                $rate = ($country->rate_to_usd > 0) ? $country->rate_to_usd : 1;
                $price = $package->price * $rate;
            } else {
                // Default Fallback
                $currency = config('paytabs.currency', 'USD');
                $price = $package->price;
            }

            // 3. Coupon / Discount Logic
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
                $discountAmount = ($price * $coupon->percent) / 100;
            }

            // Final Price Calculation
            $finalPrice = max($price - $discountAmount, 0);

            // 4. Handle Free Package (Price is 0)
            if ($finalPrice <= 0) {
                return DB::transaction(function () use ($user, $package, $couponId) {
                    UserPackage::create([
                        'user_id' => $user->id,
                        'package_id' => $package->id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at' => now()->addDays($package->validity_days),
                        'status' => 'active'
                    ]);

                    if ($couponId) {
                        Coupon::where('id', $couponId)->increment('used');
                    }

                    return response()->json([
                        'message' => 'تم تفعيل الباقة بنجاح',
                        'redirect_url' => route('payment.success') // Your success frontend route
                    ]);
                });
            }

            // 5. Create Pending Order
            $order = Order::create([
                'user_id'    => $user->id,
                'package_id' => $package->id,
                'coupon_id'  => $couponId,
                'country_id' => $user->country_id,
                'amount'     => $finalPrice,
                'currency'   => strtoupper($currency),
                'status'     => 'pending',
            ]);

            // 6. Request Payment from PayTabs
            $payment = $payTabsService->createPayment($order, $user);

            if (isset($payment['redirect_url'])) {
                return response()->json([
                    'payment_url' => $payment['redirect_url'],
                    'order_id'    => $order->id,
                    'data'        => $payment
                ]);
            }

            return response()->json([
                'error' => 'فشل في إنشاء طلب الدفع عبر PayTabs',
                'debug' => $payment
            ], 400);
        } catch (\Throwable $e) {
            Log::error('PayTabs Purchase Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع'], 500);
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
        $order = Order::find($orderId);

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
                $package = $order->package; // Assuming Order has 'package' relationship
                UserPackage::create([
                    'user_id'           => $order->user_id,
                    'package_id'        => $order->package_id,
                    'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'expires_at'        => now()->addDays($package->validity_days),
                    'status'            => 'active'
                ]);

                Log::info("Order #{$order->id} fulfilled successfully.");
            });
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

<?php

namespace App\Http\Controllers\Api\Gifts;
use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Services\PayTabsGiftService;
use App\Notifications\DynamicNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    // 1️⃣ Start Gift Payment
    public function buyGift(Request $request, PayTabsGiftService $payTabsGiftService)
    {
        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'recipient_name' => 'required|string|max:255',
            'occasion'       => 'required|string',
            'message'        => 'nullable|string'
        ]);

        try {
            $user = auth()->user();
            $package = Package::findOrFail($request->package_id);

            // 1. Determine Currency and Price (Regional Logic - Same as your code)
            $country = $user->country;

            if ($country && $country->currency_code) {
                $currency = $country->currency_code;
                $rate = ($country->rate_to_usd > 0) ? $country->rate_to_usd : 1;
                $price = $package->price * $rate;
            } else {
                $currency = config('paytabs.currency', 'USD');
                $price = $package->price;
            }

            // 🚀 التصحيح هنا: تمرير $payTabsGiftService للـ closure بدلاً من الاسم القديم
            return DB::transaction(function () use ($user, $package, $price, $currency, $request, $payTabsGiftService) {

                // 2. Create the Pending Gift Card
                $giftCard = GiftCard::create([
                    'sender_id'      => $user->id,
                    'package_id'     => $package->id,
                    'minutes'        => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'price'          => $price,
                    'recipient_name' => $request->recipient_name,
                    'occasion'       => $request->occasion,
                    'message'        => $request->message,
                    'payment_status' => 'pending',
                    'status'         => 'active'
                ]);

                // 3. Create Pending Order (Linked to the Gift Card)
                $order = Order::create([
                    'user_id'      => $user->id,
                    'package_id'   => $package->id,
                    'country_id'   => $user->country_id,
                    'amount'       => $price,
                    'currency'     => strtoupper($currency),
                    'status'       => 'pending',
                    'is_gift'      => true,          // 👈 تمييز أن هذا الطلب هدية
                    'gift_card_id' => $giftCard->id, // 👈 ربط الطلب بالهدية
                ]);

                // 4. Request Payment from PayTabs
                // 🚀 التصحيح هنا: استخدام المتغير الصحيح واسم الدالة الصحيحة
                $payment = $payTabsGiftService->createGiftPayment($order, $user);

                if (isset($payment['redirect_url'])) {
                    return response()->json([
                        'status'      => true,
                        'payment_url' => $payment['redirect_url'],
                        'order_id'    => $order->id,
                        'data'        => $payment
                    ]);
                }

                return response()->json([
                    'error' => 'فشل في إنشاء طلب الدفع عبر PayTabs',
                    'debug' => $payment
                ], 400);
            });

        } catch (\Throwable $e) {
            Log::error('PayTabs Gift Purchase Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء تجهيز الهدية'], 500);
        }
    }

    // 2️⃣ PayTabs Server Callback (IMPORTANT)
    public function handleCallback(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['cartId'] ?? $payload['cart_id'] ?? null;
        $status  = $payload['respStatus'] ?? $payload['payment_result']['response_status'] ?? null;

        if ($status === 'A' && $orderId) {
            $order = Order::with('giftCard')->find($orderId);

            // نتحقق من الطلب، وأنه لم يتم الدفع مسبقاً، وأنه طلب (هدية)
            if ($order && $order->status !== 'paid' && $order->is_gift) {
                DB::transaction(function () use ($order, $payload) {

                    // A. Update the Order Status
                    $order->update([
                        'status' => 'paid',
                        'transaction_id' => $payload['tranRef'] ?? $payload['tran_ref'] ?? null
                    ]);

                    // B. Generate Coupon and Update Gift Card
                    $giftCard = $order->giftCard;
                    if ($giftCard) {
                        // توليد كود سري مميز للهدية (مثال: GFT-A1B2C3)
                        $couponCode = 'GFT-' . strtoupper(Str::random(6));

                        $giftCard->update([
                            'payment_status' => 'paid',
                            'coupon_code'    => $couponCode,
                        ]);

                        Log::info("Gift Order #{$order->id} paid successfully. Gift Code generated: {$couponCode}");
                    }
                });
            }
        }

        return response('OK');
    }

    // 3️⃣ Frontend Redirect (WebView / Browser)
// 3️⃣ تحديث دالة الاستجابة (لتجهيز رابط البطاقة الساحر)
    public function handleResponse(Request $request)
    {
        $data = $request->all();
        $status  = $data['respStatus'] ?? null;
        $cartId  = $data['cartId'] ?? null;

        if ($status === 'A') {
            $order = Order::with('giftCard')->find($cartId);
            $giftCard = $order ? $order->giftCard : null;

            if ($giftCard && $giftCard->coupon_code) {

                $couponCode = $giftCard->coupon_code;

                // 🌟 هنا السحر: توليد الرابط الذي يعرض البطاقة بكامل بياناتها
                $cardLink = route('web.gifts.card.show', ['code' => $couponCode]);

                // رسالة واتساب جاهزة تحتوي على الرابط
                $whatsappMessage = urlencode("أهديتك باقة دقائق في التطبيق! 🎁 اضغط على الرابط لفتح هديتك واستلامها: \n {$cardLink}");
                $whatsappLink = "https://wa.me/?text={$whatsappMessage}";

                return view('payments.gift_success', compact('couponCode', 'whatsappLink', 'cardLink'));
            }
        }
        return view('payments.failed');
    }

    // 🌟 5️⃣ الدالة الجديدة: عرض البطاقة للمستلم (عندما يضغط على رابط الواتساب)
    public function showGiftCard($code)
    {
        // جلب الهدية مع بيانات المرسل لكي نظهرها في البطاقة
        $giftCard = GiftCard::with('sender')->where('coupon_code', $code)->firstOrFail();

        // هل تم استلامها مسبقاً؟
        $isClaimed = $giftCard->status === 'claimed';

        return view('gifts.card_view', compact('giftCard', 'isClaimed'));
    }


    public function claimGift(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            $giftCard = GiftCard::with('sender')->where('coupon_code', $request->coupon_code)->first();

            if (!$giftCard) {
                return response()->json(['error' => 'كود الهدية غير صحيح أو غير موجود.'], 404);
            }

            // 🚫 السطر الجديد: منع المُرسل من استلام هديته بنفسه
            if ($giftCard->sender_id === $user->id) {
                return response()->json(['error' => 'لا يمكنك استلام الهدية التي قمت بإرسالها بنفسك.'], 400);
            }

            if ($giftCard->payment_status !== 'paid') {
                return response()->json(['error' => 'لم يتم إكمال عملية الدفع لهذه الهدية.'], 400);
            }

            if ($giftCard->status === 'claimed') {
                return response()->json(['error' => 'عذراً، تم استخدام هذه الهدية مسبقاً.'], 400);
            }

            // (اختياري) التأكد من أن المستلم ليس لديه باقة نشطة من نفس النوع
            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $giftCard->package_id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل، لا يمكنك استلام هذه الباقة الآن.'], 400);
            }

            DB::transaction(function () use ($giftCard, $user) {
                // A. تحديث الهدية لتصبح مستلمة ومحترقة
                $giftCard->update([
                    'status'             => 'claimed',
                    'claimed_by_user_id' => $user->id,
                    'claimed_at'         => now(),
                ]);

                // B. تفعيل باقة المستلم (بنفس طريقتك الأصلية)
                $package = Package::find($giftCard->package_id);

                UserPackage::create([
                    'user_id'           => $user->id,
                    'package_id'        => $package->id,
                    'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'expires_at'        => now()->addDays($package->validity_days),
                    'status'            => 'active'
                ]);

                // C. 🚀 إرسال إشعار للمرسل بأن هديته تم قبولها
                if ($giftCard->sender) {
                    $giftCard->sender->notify(new DynamicNotification(
                        'تم قبول هديتك! 🎉',
                        "قام {$user->name} بتفعيل الباقة التي أهديتها له.",
                        'gift_claimed',
                        ['gift_card_id' => $giftCard->id]
                    ));
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'مبروك! تم تفعيل الباقة المهداة بنجاح.',
                'data'    => [
                    'sender_name' => optional($giftCard->sender)->name,
                    'message'     => $giftCard->message
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Claim Gift Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء استلام الهدية'], 500);
        }
    }

    public function myGifts()
    {
        try {
            $user = auth()->user();
            $sentGifts = GiftCard::with('claimer:id,name') // جلب بيانات المستلم الفعلي إن وُجد
                ->where('sender_id', $user->id)
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($gift) {
                    return [
                        'id'             => $gift->id,
                        'recipient_name' => $gift->recipient_name, // الاسم الذي كتبه عند الشراء
                        'claimed_by'     => $gift->claimer ? $gift->claimer->name : null, // من استلمها فعلياً بالتطبيق
                        'minutes'        => $gift->minutes,
                        'occasion'       => $gift->occasion,
                        'coupon_code'    => $gift->coupon_code,
                        'status'         => $gift->status, // 'active' (لم تُستلم بعد) أو 'claimed' (تم استلامها)
                        'created_at'     => $gift->created_at->format('Y-m-d h:i A'),
                        'share_link'     => url("/gifts/card/" . $gift->coupon_code), // رابط المشاركة لو أراد نسخه مجدداً
                    ];
                });

            // 🎁 2. الهدايا التي استلمها هذا الطالب (Received Gifts)
            $receivedGifts = GiftCard::with('sender:id,name') // جلب بيانات الصديق الذي أرسلها
                ->where('claimed_by_user_id', $user->id)
                ->orderBy('claimed_at', 'desc')
                ->get()
                ->map(function ($gift) {
                    return [
                        'id'          => $gift->id,
                        'sender_name' => $gift->sender ? $gift->sender->name : 'صديق',
                        'minutes'     => $gift->minutes,
                        'occasion'    => $gift->occasion,
                        'message'     => $gift->message,
                        'claimed_at'  => $gift->claimed_at->format('Y-m-d h:i A'),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب سجل الهدايا بنجاح',
                'data' => [
                    'sent'     => $sentGifts,
                    'received' => $receivedGifts,
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Get My Gifts Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء جلب الهدايا'], 500);
        }
    }
}

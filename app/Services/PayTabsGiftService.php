<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayTabsGiftService
{
    /**
     * إنشاء طلب دفع مخصص للهدايا فقط
     */
    public function createGiftPayment($order, $user)
    {
        // 🚀 استخدام الروابط الخاصة بالهدايا التي برمجناها
        $callbackUrl = str_replace('http://', 'https://', route('api.gifts.payment.callback'));
        $returnUrl   = str_replace('http://', 'https://', route('web.gifts.payment.response'));

        $response = Http::withHeaders([
            'authorization' => config('paytabs.server_key'),
            'content-type'  => 'application/json',
        ])->post(config('paytabs.baseUrl') . 'payment/request', [
            "profile_id"       => (int) config('paytabs.profile_id'),
            "tran_type"        => "sale",
            "tran_class"       => "ecom",
            "cart_id"          => (string) $order->id,
            "cart_description" => "Gift Package Purchase #" . $order->id, // وصف مميز للهدية
            "cart_currency"    => $order->currency,
            "cart_amount"      => (float) $order->amount,
            "callback"         => $callbackUrl, // رابط السيرفر للهدايا
            "return"           => $returnUrl,   // رابط صفحة النجاح للهدايا
            "customer_details" => [
                "name"    => $user->name,
                "email"   => $user->email,
                "phone"   => $user->phone ?? "0000000000",
                "street1" => "Street Address",
                "city"    => "Cairo", // أو مدينة المستخدم إذا كانت متوفرة
                "country" => "EG",    // أو دولة المستخدم
                "state"   => "CA"
            ]
        ]);

        return $response->json();
    }
}

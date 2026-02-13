<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('PAYMOB_API_KEY');
    }

    public function authenticate()
    {
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => $this->apiKey
        ]);

        return $response['token'];
    }

    public function createOrder($token, $amount, $currency = "EGP")
    {
        $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
            'auth_token' => $token,
            'delivery_needed' => false,
            'amount_cents' => $amount * 100,
            'currency' => $currency,
            'items' => []
        ]);

        return $response;
    }

    public function generatePaymentKey($token, $orderId, $amount, $user, $currency, $integrationId)
    {
        // Ensure amount is a clean integer string
        $amountCents = (int) round($amount * 100);

        $response = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'auth_token' => $token,
            'amount_cents' => (int)$amountCents,
            'expiration' => 3600,
            'order_id' => $orderId,
            'billing_data' => [
                "first_name" => $user->name ?: 'Customer',
                "last_name"  => 'User', // Paymob requires both
                "email"      => $user->email,
                "phone_number" => $user->phone ?? "01000000000",
                "apartment"  => "NA",
                "floor"      => "NA",
                "street"     => "NA",
                "building"   => "NA",
                "shipping_method" => "NA",
                "postal_code" => "NA",
                "city"       => "Cairo",
                "country"    => strtoupper(trim($user->country_code ?? 'EG')),
                "state"      => "NA"
            ],
            'currency' => $currency,
            'integration_id' => (int) $integrationId
        ]);

        if (!$response->successful()) {
            Log::error('Paymob Payment Key Error', $response->json());
            throw new \Exception("Paymob failed to generate payment key");
        }

        return $response['token'];
    }

    public function getIframeUrl($iframeId, $paymentToken)
    {
        return "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
    }
public function validateHmac(array $data)
{
    $hmac = $data['hmac'] ?? '';

    // الترتيب الأبجدي الدقيق المطلوب لطلب الـ GET
    $keys = [
        'amount_cents',
        'created_at',
        'currency',
        'error_occured',
        'has_parent_transaction',
        'id',
        'integration_id',
        'is_3d_secure',
        'is_auth',
        'is_capture',
        'is_refunded',
        'is_standalone_payment',
        'is_voided',
        'order',
        'owner',
        'pending',
        'source_data_pan',
        'source_data_sub_type',
        'source_data_type',
        'success'
    ];

    $concatenatedString = "";

    foreach ($keys as $key) {
        $val = '';

        // معالجة خاصة لحقول source_data لأنها تأتي مسطحة (Flattened) في الرابط
        if (in_array($key, ['source_data_pan', 'source_data_sub_type', 'source_data_type'])) {
            $val = $data[$key] ?? '';
        } else {
            $val = $data[$key] ?? '';
        }

        // تحويل القيم المنطقية إلى نصوص كما يطلب باي موب
        if ($val === true || $val === "true") {
            $val = "true";
        } elseif ($val === false || $val === "false") {
            $val = "false";
        }

        $concatenatedString .= $val;
    }

    $secret = env('PAYMOB_HMAC_SECRET');
    $calculatedHmac = hash_hmac('sha512', $concatenatedString, $secret);

    return hash_equals($hmac, $calculatedHmac);
}
}

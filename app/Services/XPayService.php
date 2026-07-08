<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XPayService
{
    /**
     * Create a payment request for XPay Variable Amount.
     * 
     * @param \App\Models\Order $order
     * @param \App\Models\User $user
     * @return array|null
     */
    public function createPayment($order, $user)
    {
        try {
            $baseUrl = config('xpay.base_url');
            $endpoint = rtrim($baseUrl, '/') . '/payments/pay/variable-amount';

            $response = Http::withHeaders([
                'x-api-key' => config('xpay.api_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($endpoint, [
                'billing_data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone ?? '01000000000',
                ],
                'amount' => (float) $order->amount,
                'currency' => strtoupper($order->currency ?? 'EGP'),
                'variable_amount_id' => (int) config('xpay.variable_amount_id'),
                'community_id' => config('xpay.community_id'),
                'pay_using' => 'card',
                'custom_fields' => [
                    [
                        'field_label' => 'Order ID',
                        'field_value' => (string) $order->id
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('XPay API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('XPay Service Exception: ' . $e->getMessage());
            return null;
        }
    }
}

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
            $baseUrl = config('xpay.base_url', 'https://api.xpay.app/');
            $endpoint = rtrim($baseUrl, '/') . '/checkout/sessions';

            // Amount usually handled in the smallest unit (piasters) if mimicking Stripe
            $amountInCents = (int) round((float) $order->amount * 100);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('xpay.secret_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($endpoint, [
                'customerDetails' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '01000000000',
                ],
                'lineItems' => [
                    [
                        'priceData' => [
                            'currency' => strtoupper($order->currency ?? 'EGP'),
                            'productData' => [
                                'name' => 'Order #' . $order->id,
                                'description' => 'Order Payment ID: ' . $order->id,
                            ],
                            'unitAmount' => $amountInCents,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'metadata' => [
                    'orderId' => (string) $order->id
                ],
                'afterCompletion' => [
                    'type' => 'redirect',
                    'redirect' => [
                        'url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id
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

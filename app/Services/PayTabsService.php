<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayTabsService
{

// Inside PayTabsService.php
public function createPayment($order, $user)
{
    // Use the ngrok HTTPS URL directly from config or env
    $callbackUrl = str_replace('http://', 'https://', route('api.paytabs.callback'));
    $returnUrl = str_replace('http://', 'https://', route('api.paytabs.response'));

    $response = Http::withHeaders([
            'authorization' => config('paytabs.server_key'),
            'content-type'  => 'application/json',
        ])->post(config('paytabs.baseUrl') . 'payment/request', [
            "profile_id" => (int) config('paytabs.profile_id'),
            "tran_type" => "sale",
            "tran_class" => "ecom",
            "cart_id" => (string) $order->id,
            "cart_description" => "Package Purchase #" . $order->id,
            "cart_currency" => $order->currency,
            "cart_amount" => (float) $order->amount,
            "callback" => $callbackUrl, // Must be HTTPS, No Port
            "return"   => $returnUrl,   // Must be HTTPS, No Port
            "customer_details" => [
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone ?? "0000000000",
                "street1" => "Street Address",
                "city" => "Cairo",
                "country" => "EG",
                "state" => "CA"
            ]
        ]);
    return $response->json();
}
}

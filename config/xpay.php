<?php

return [
    'secret_key'      => env('XPAY_SECRET_KEY', ''),
    'publishable_key' => env('XPAY_PUBLISHABLE_KEY', ''),
    'webhook_secret'  => env('XPAY_WEBHOOK_SECRET', ''),
    'base_url'        => env('XPAY_BASE_URL', 'https://api.xpay.app/'),
];


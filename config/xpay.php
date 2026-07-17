<?php

return [
    'secret_key'      => env('XPAY_SECRET_KEY', 'sk_test_fcjDFmwJ82lJkW3OXil8Vasklwa7zswM'),
    'publishable_key' => env('XPAY_PUBLISHABLE_KEY', 'pk_test_RCSFF3qO89FPvez46ztaCqCJwwFKvsK'),
    'webhook_secret'  => env('XPAY_WEBHOOK_SECRET', 'whsec_EgdMDpBdFFyeytyhT3Npmx59skus0HK8'),
    'base_url'        => env('XPAY_BASE_URL', 'https://api.xpay.app/'),
];
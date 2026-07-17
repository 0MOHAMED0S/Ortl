<?php

return [
    'profile_id'  => env('PAYTABS_PROFILE_ID'),
    'server_key'  => env('PAYTABS_SERVER_KEY'),
    'region'      => env('PAYTABS_REGION'),
    'currency'    => env('PAYTABS_CURRENCY', 'EGP'),
    'baseUrl'     => env('PAYTABS_BASE_URL', 'https://secure.paytabs.com/'),

    'camel_case'  => false, // Set to true if you want response keys in CamelCase
];

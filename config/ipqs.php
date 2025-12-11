<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IPQS API Key
    |--------------------------------------------------------------------------
    |
    | Your API key from IPQualityScore. You can get one from your dashboard:
    | https://www.ipqualityscore.com/user/dashboard
    |
    */
    'api_key' => env('IPQS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for the HTTP request to the IPQS API in seconds.
    |
    */
    'timeout' => 10.0,

    /*
    |--------------------------------------------------------------------------
    | Default API Options
    |--------------------------------------------------------------------------
    |
    | You can set default options for the API calls here. These will be
    | merged with the options you pass to the methods.
    |
    */
    'defaults' => [
        'ip' => [
            'strictness' => 1,
            'allow_public_access_points' => true,
            'lighter_penalties' => true,
        ],
        'email' => [
            'timeout' => 7,
        ],
        'phone' => [
            // 'country_code' => 'US',
        ],
    ],
];

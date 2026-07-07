<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TouchEstate API Credentials
    |--------------------------------------------------------------------------
    |
    | Your TouchEstate access key credentials. You can generate these from
    | your workspace settings in the TouchEstate dashboard.
    |
    */

    'public_key' => env('TOUCHESTATE_PUBLIC_KEY', ''),
    'secret_key' => env('TOUCHESTATE_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the TouchEstate API. You typically don't need to
    | change this unless you're using a custom deployment.
    |
    */

    'base_url' => env('TOUCHESTATE_BASE_URL', 'https://touchestate.io'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client timeout settings in seconds.
    |
    */

    'timeout'         => env('TOUCHESTATE_TIMEOUT', 30),
    'connect_timeout' => env('TOUCHESTATE_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Disable SSL certificate verification for local development on Windows.
    | Never set to false in production.
    |
    */

    'verify_ssl' => env('TOUCHESTATE_VERIFY_SSL', true),
];

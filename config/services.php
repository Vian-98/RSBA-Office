<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dms' => [
        'base_url' => env('DMS_MIDDLEWARE_API_URL', 'http://127.0.0.1:8000'),
        'email' => env('DMS_MIDDLEWARE_EMAIL', 'admin@dms.local'),
        'password' => env('DMS_MIDDLEWARE_PASSWORD', 'password'),
    ],

    'docstore' => [
        'base_url'     => env('DOCSTORE_BASE_URL', 'http://localhost:8000'),
        'api_url'      => env('DOCSTORE_API_URL', 'http://localhost:8000/api'),
        'client_id'    => env('DOCSTORE_OAUTH_CLIENT_ID', ''),
        'client_secret'=> env('DOCSTORE_OAUTH_CLIENT_SECRET', ''),
        'hmac_secret'  => env('DOCSTORE_HMAC_SECRET', ''),
        'verify_ssl'   => env('DOCSTORE_VERIFY_SSL', false),
    ],
];

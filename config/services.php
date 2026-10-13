<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'flutterwave' => [
        'public_key' => env('FLW_PUBLIC_KEY'),
        'secret_key' => env('FLW_SECRET_KEY'),
    ],

    'iotec' => [
        // ioTec Pay (https://pay.iotec.io) — Mobile Money + card collections.
        // Credentials arrive by email after ioTec merchant onboarding.
        'client_id' => env('IOTEC_CLIENT_ID'),
        'client_secret' => env('IOTEC_CLIENT_SECRET'),
        'wallet_id' => env('IOTEC_WALLET_ID'),
        'base_url' => env('IOTEC_BASE_URL', 'https://pay.iotec.io'),
        'auth_url' => env('IOTEC_AUTH_URL', 'https://id.iotec.io/connect/token'),
        // Shared secret you also enter in the ioTec portal's Callback URLs card
        // (Security Headers). Callbacks carrying a wrong token are rejected.
        'callback_token' => env('IOTEC_CALLBACK_TOKEN'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'momo' => [
        // MTN Mobile Money merchant code shown to customers for manual payments.
        'merchant_code' => env('MOMO_MERCHANT_CODE', '236512'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];

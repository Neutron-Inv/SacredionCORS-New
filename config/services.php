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
        'key' => env('POSTMARK_API_KEY'),
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

    'cors_ftp' => [
        'host' => env('CORS_FTP_HOST', '101.133.152.93'),
        'port' => env('CORS_FTP_PORT', 21),
        'username' => env('CORS_FTP_USER', 'Nigeria'),
        'password' => env('CORS_FTP_PASSWORD', 'Nigeria'),
        'passive' => env('CORS_FTP_PASSIVE', true),
        'root' => env('CORS_FTP_ROOT', '/RINEX_1_2'),
        'retention_months' => (int) env('CORS_RETENTION_MONTHS', 3),
        'sync_days' => (int) env('CORS_SYNC_DAYS', 2),
    ],

    'sogpos' => [
        'google_play_url' => env('SOGPOS_GOOGLE_PLAY_URL'),
    ],

];

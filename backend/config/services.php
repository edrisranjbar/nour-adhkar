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
        // .env.example documents RESEND_API_KEY; RESEND_KEY is kept for existing deployments.
        'key' => env('RESEND_KEY', env('RESEND_API_KEY')),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID', '1234567890123456789012345678901234'),
    ],

    'google' => [
        // Comma-separated OAuth client ids whose ID tokens are accepted (Android app's web client id).
        'client_ids' => env('GOOGLE_CLIENT_IDS', ''),
        // Google's public signing keys, tried in order. Google itself is unreachable from Iran,
        // so a mirror refreshed by a GitHub Action (Nour-Adhkar-App/.github/workflows/google-jwks.yml) follows.
        // Shared secret the GitHub workflow uses to push keys to POST /api/internal/google-jwks.
        'jwks_push_token' => env('GOOGLE_JWKS_PUSH_TOKEN', ''),
        'jwks_urls' => env('GOOGLE_JWKS_URLS', 'https://www.googleapis.com/oauth2/v3/certs,https://raw.githubusercontent.com/edrisranjbar/Nour-Adhkar-App/main/google-jwks.json'),
    ],

];

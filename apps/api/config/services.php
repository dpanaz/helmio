<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more.
    |
    */

    'alpha_vantage' => [
    'key' => env('ALPHA_VANTAGE_API_KEY'),
    'base_url' => env(
        'ALPHA_VANTAGE_BASE_URL',
        'https://www.alphavantage.co/query'
    ),
],

    'stripe' => [
        'key' => env('STRIPE_KEY'),

        'secret' => env('STRIPE_SECRET'),

        'webhook_secret' =>
            env('STRIPE_WEBHOOK_SECRET'),

        'prices' => [
            'monthly' =>
                env('STRIPE_PRICE_MONTHLY'),

            'annual' =>
                env('STRIPE_PRICE_ANNUAL'),
        ],

        'trial_days' =>
            (int) env(
                'HELMIO_TRIAL_DAYS',
                14
            ),
    ],

    'snaptrade' => [
        'client_id' =>
            env('SNAPTRADE_CLIENT_ID'),

        'consumer_key' =>
            env('SNAPTRADE_CONSUMER_KEY'),
    ],

    'postmark' => [
        'key' =>
            env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' =>
            env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' =>
            env('AWS_ACCESS_KEY_ID'),

        'secret' =>
            env('AWS_SECRET_ACCESS_KEY'),

        'region' =>
            env(
                'AWS_DEFAULT_REGION',
                'us-east-1'
            ),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' =>
                env('SLACK_BOT_USER_OAUTH_TOKEN'),

            'channel' =>
                env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twelve_data' => [
        'key' =>
            env('TWELVE_DATA_API_KEY'),

        'base_url' =>
            env(
                'TWELVE_DATA_BASE_URL',
                'https://api.twelvedata.com'
            ),
    ],

];
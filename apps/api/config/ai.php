<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portfolio chat provider
    |--------------------------------------------------------------------------
    |
    | Supported values:
    | - fake
    | - openai
    |
    */

    'portfolio_chat_provider' => env(
        'AI_PORTFOLIO_CHAT_PROVIDER',
        'fake',
    ),

    /*
    |--------------------------------------------------------------------------
    | OpenAI configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),

        'base_url' => env(
            'OPENAI_BASE_URL',
            'https://api.openai.com/v1',
        ),

        'model' => env(
            'OPENAI_PORTFOLIO_CHAT_MODEL',
            'gpt-5',
        ),

        'timeout_seconds' => (int) env(
            'OPENAI_TIMEOUT_SECONDS',
            90,
        ),

        'connect_timeout_seconds' => (int) env(
            'OPENAI_CONNECT_TIMEOUT_SECONDS',
            15,
        ),

        'max_output_tokens' => (int) env(
            'OPENAI_MAX_OUTPUT_TOKENS',
            1200,
        ),

        'reasoning_effort' => env(
            'OPENAI_REASONING_EFFORT',
            'low',
        ),

        'store_responses' => (bool) env(
            'OPENAI_STORE_RESPONSES',
            false,
        ),
    ],
];
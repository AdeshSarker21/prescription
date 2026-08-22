<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Medical Assistant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the AI provider and behavior for the medical assistant.
    | When OPENAI_API_KEY is set, the system uses OpenAI's API.
    | Otherwise, it falls back to a smart rule-based system using the database.
    |
    */

    'provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 1500),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),
    ],

    'disclaimer' => 'This is a clinical decision support tool. Final diagnosis and treatment decisions rest with the treating physician.',

    'max_context_medicines' => 50,
    'max_context_complaints' => 100,
    'max_context_tests' => 50,
    'max_context_history' => 5,

];

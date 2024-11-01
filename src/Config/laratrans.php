<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'table_name' => 'laratrans_translations',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 hour in seconds
        'prefix' => 'laratrans_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locales' => [
        'default' => 'en',
        'supported' => ['en', 'es', 'fr', 'ar'], // Add your supported locales
        'fallback_locale' => 'en',
        'auto_fallback' => true, // Whether to automatically fall back to default locale
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'default_rules' => [
            'min' => 1,
            'max' => 255,
            'required_locales' => [], // Locales that must have translations
        ],
        // Property-specific rules
        'properties' => [
            // Example: 'title' => ['min' => 3, 'max' => 100, 'required_locales' => ['en', 'es']]
        ],
    ],
];
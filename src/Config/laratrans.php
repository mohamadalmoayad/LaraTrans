<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'mode' => 'single_table',   // 'single_table' or 'dedicated_tables'
        'table_prefix' => 'trans_',  // Prefix for dedicated translation tables
        'table_name' => 'laratrans_translations', // Single table name (used in single_table mode)
    ],

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
    | Models Configuration
    |--------------------------------------------------------------------------
    */
    'models' => [
        // Model class for single table translations
        'translation' => \Almoayad\LaraTrans\Models\Translation::class,

        // Base model for dedicated table translations
        'dedicated_translation' => \Almoayad\LaraTrans\Models\DedicatedTranslation::class,
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
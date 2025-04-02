<p align="center"><a href="https://www.modesignsstudio.com" target="_blank"><img src="https://www.modesignsstudio.com/_next/image?url=%2Fimages%2Flogo%2Fmo-designs-logo.gif&w=256&q=75" width="250" alt="Mo Designs Studio Logo"></a></p>

# LaraTrans

**LaraTrans** is a Laravel package that simplifies the process of handling translations for your models. It allows you to easily create, update, and delete translations for specific model properties, making your application ready for multilingual support with minimal effort.

## Version 3.0 Updates
- **Multiple Storage Strategies**  
  - Single table mode (default)  
  - Dedicated tables mode for better organization and performance
- **Migration System**  
  - Easily switch between storage strategies  
  - Migrate existing translations between strategies  
  - Clean up unused translation tables
- **Enhanced Validation**  
  - Property-specific validation rules  
  - Required locales per property  
  - Unique translation validation
- **Improved API**  
  - Strategy-based architecture  
  - Better performance and reliability  
  - Extended translation methods
- Added support for Laravel 11

## Requirements
- PHP 8.2 or higher  
- Laravel 8.0 or higher (including Laravel 11)

## Installation

You can install the package via Composer:

```bash
composer require almoayad/laratrans
```

Next, publish the migration and configuration files:

```bash
php artisan vendor:publish --provider="Almoayad\LaraTrans\LaraTransServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Almoayad\LaraTrans\LaraTransServiceProvider" --tag="config"
```

Then, run the migration:

```bash
php artisan migrate
```

## Configuration

After publishing the configuration file, you can customize various aspects of LaraTrans in `config/laratrans.php`:

```php
return [
    // Storage strategies
    'storage' => [
        'mode' => 'single_table', // or 'dedicated_tables'
        'table_prefix' => 'trans_',
        'table_name' => 'laratrans_translations',
    ],
    
    // Caching
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 hour
        'prefix' => 'laratrans_',
    ],

    // Locales
    'locales' => [
        'default' => 'en',
        'supported' => ['en', 'es', 'fr', 'ar'],
        'fallback_locale' => 'en',
        'auto_fallback' => true,
    ],

    // Models
    'models' => [
        'translation' => \Almoayad\LaraTrans\Models\Translation::class,
        'dedicated_translation' => \Almoayad\LaraTrans\Models\DedicatedTranslation::class,
    ],

    // Validation
    'validation' => [
        'default_rules' => [
            'min' => 1,
            'max' => 255,
            'required_locales' => [], // Locales that must have translations
        ],
        'properties' => [
            // 'title' => [
            //     'min' => 3,
            //     'max' => 100,
            //     'required_locales' => ['en', 'es']
            // ],
        ],
    ],
];
```

## Usage

### Step 1: Add the HasTranslations Trait

Add the HasTranslations trait to your model:

```php
use Almoayad\LaraTrans\Traits\HasTranslations;

class SomeModel extends Model
{
    use HasTranslations;
}
```

### Step 2: Create Translations

You can create translations in several ways:

```php
// Method 1: Bulk creation via request
$model = SomeModel::create($request->all()); // Automatically handles 'translations' array in request

// Method 2: Bulk creation directly
$model = SomeModel::create($request->except('translations'));
$translations = [
    ['locale' => 'ar', 'property_name' => 'name', 'value' => 'أسود'],
    ['locale' => 'en', 'property_name' => 'name', 'value' => 'black'],
    ['locale' => 'fr', 'property_name' => 'name', 'value' => 'noir'],
];
$model->updateModelTranslations($translations);

// Method 3: Individual creation
$model->setTranslation('name', 'black', 'en');
```

### Step 3: Retrieve Translations

```php
// Get translation for current locale
$translation = $model->filterTranslation('name');

// Get translation for specific locale
$translation = $model->filterTranslation('name', 'fr');

// Get all translations for current locale
$translations = $model->localeTranslation()->get();

// Check if translation exists
$exists = $model->checkTranslationExists('name', 'en');
```

### Step 4: Query with Translations

```php
// Query models with translations
$models = SomeModel::withTranslation('name')
    ->whereTranslation('name', 'black')
    ->get();
```

## Storage Strategies

### Single Table Mode (default)
All translations are stored in one table with polymorphic relationships.  
- Good for applications with fewer models needing translation  
- Simple setup and migration  
- Uses the standard `laratrans_translations` table

### Dedicated Tables Mode
Each model has its own translation table for better performance and organization.  
- Better for applications with many translatable models  
- Improved query performance  
- Better database organization  
- Creates tables like `trans_products_translations`, `trans_categories_translations`, etc.

### Switching Storage Strategies

```bash
# Switch from single table to dedicated tables
php artisan laratrans:migrate-strategy

# Switch from dedicated tables to single table
php artisan laratrans:migrate-strategy --reverse

# Clean up old tables after migration (optional)
php artisan laratrans:cleanup --single
php artisan laratrans:cleanup --dedicated
```

### Creating Dedicated Translation Tables

```bash
# Create a dedicated translation table for a model
php artisan laratrans:table Product
```

## Validation

LaraTrans includes a robust validation system:

### Global Validation Rules

```php
'validation' => [
    'default_rules' => [
        'min' => 1,
        'max' => 255,
        'required_locales' => ['en'], // All translations must have English
    ],
],
```

### Property-Specific Rules

```php
'properties' => [
    'title' => [
        'min' => 3,
        'max' => 100,
        'required_locales' => ['en', 'es'] // Title must have English and Spanish
    ],
    'description' => [
        'min' => 10,
        'max' => 1000
    ],
],
```

### Validation in Practice

```php
// Validation happens automatically
try {
    $model->setTranslation('title', 'Too short', 'en');
} catch (ValidationException $e) {
    // Handle validation error
}

// Bulk translations are also validated
$model->create([
    'translations' => [
        ['locale' => 'en', 'property_name' => 'title', 'value' => 'Valid title'],
        ['locale' => 'es', 'property_name' => 'title', 'value' => 'Título válido']
    ]
]);
```

## Caching

LaraTrans includes a powerful caching system for improved performance. The TranslationCache trait provides automatic caching of translations with smart cache invalidation.

### Setting Up Caching

1. Add the TranslationCache trait to your model (after HasTranslations):
```php
use Almoayad\LaraTrans\Traits\HasTranslations;
use Almoayad\LaraTrans\Traits\TranslationCache;

class Product extends Model
{
    use HasTranslations, TranslationCache;
}
```

2. Configure caching options in `config/laratrans.php`:
```php
'cache' => [
    'enabled' => true,
    'duration' => 3600, // Cache duration in seconds
    'prefix' => 'laratrans_', // Cache key prefix
],
```

### Using Cached Translations

```php
// Get cached translation for current locale
$translation = $model->getCachedTranslation('title');

// Get cached translation for specific locale
$translation = $model->getCachedTranslation('title', 'es');

// Setting translations (automatically handles cache)
$model->setTranslation('title', 'New Title', 'en');
```

### Cache Invalidation

The cache is automatically invalidated in the following scenarios:
- When setting new translations via `setTranslation()`
- When deleting the model (all related translations are removed from cache)
- When the cache duration expires
- When bulk updating translations

## Automatic Features

LaraTrans automatically:
- Validates translations based on your configuration
- Creates translations during model creation
- Updates translations during model updates
- Deletes translations when the model is deleted
- Falls back to the default locale when a translation is missing (configurable)
- Handles migrations between storage strategies

## Customization

You can customize LaraTrans by:
- Extending the HasTranslations trait
- Modifying the configuration file
- Creating custom validation rules
- Adjusting the migration file
- Creating dedicated translation tables for specific models

## Testing the Package Locally

1. Add to your Laravel application's `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "../path/to/LaraTrans"
    }
],
```
2. Then run:
```bash
composer require almoayad/laratrans
```

## License

LaraTrans is open-source software licensed under the MIT license.

## Feedback and Contributions

If you have any feedback or suggestions, feel free to open an issue or submit a pull request. Contributions are more than welcome!

## Version History

- 3.0.0: Added storage strategies, migration system, enhanced validation, and Laravel 11 support
- 2.0.0: Added Laravel 11 support, validation system, and configuration options
- 1.0.4: Initial release with basic translation functionality

## Developed with ❤️ by Mohamad Almoayad.

---

This package is maintained with pride by [Mo Designs Studio](https://www.modesignsstudio.com).
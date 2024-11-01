<p align="center"><a href="https://www.modesigns.studio" target="_blank"><img src="https://www.modesigns.studio/_next/image?url=%2Fimages%2Flogo%2Fmo-designs-logo.gif&w=256&q=75" width="250" alt="Mo Designs Studio Logo"></a></p>

# LaraTrans

**LaraTrans** is a Laravel package that simplifies the process of handling translations for your models. It allows you to easily create, update, and delete translations for specific model properties, making your application ready for multilingual support with minimal effort.

## Version 2.0 Updates
- Added support for Laravel 11
- Added robust validation system
- Added comprehensive configuration options
- Added automatic locale fallback support
- Added validation rules for translations
- Added support for property-specific validation rules
- Improved performance and reliability

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
    'table_name' => 'laratrans_translations',
    
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 hour
        'prefix' => 'laratrans_',
    ],

    'locales' => [
        'default' => 'en',
        'supported' => ['en', 'es', 'fr', 'ar'],
        'fallback_locale' => 'en',
        'auto_fallback' => true,
    ],

    'validation' => [
        'default_rules' => [
            'min' => 1,
            'max' => 255,
            'required_locales' => [],
        ],
        'properties' => [
            'title' => [
                'min' => 3,
                'max' => 100,
                'required_locales' => ['en', 'es']
            ],
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
// Method 1: Bulk creation
$model = SomeModel::create($request->except('translations'));
$translations = [
    ['locale' => 'ar', 'property_name' => 'name', 'value' => 'أسود'],
    ['locale' => 'en', 'property_name' => 'name', 'value' => 'black'],
    ['locale' => 'fr', 'property_name' => 'name', 'value' => 'noir'],
];

// Method 2: Individual creation
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
```

### Step 4: Query with Translations

```php
// Query models with translations
$models = SomeModel::withTranslation('name')
    ->whereTranslation('name', 'black')
    ->get();
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

### Cache Keys Structure

Cache keys are automatically generated using the following format:
```
{prefix}_{table}_{model_id}_{locale}_{property}
```

For example:
```
laratrans_products_1_en_title
```

### Performance Considerations

- First access: Performs database query and caches result
- Subsequent accesses: Returns cached value without database query
- Memory usage: One cache entry per translation
- Recommended for:
  - Frequently accessed translations
  - Static content that rarely changes
  - High-traffic applications

### Cache Driver Compatibility

TranslationCache works with any Laravel-supported cache driver:
- Redis (recommended for production)
- Memcached
- File
- Database
- Array (useful for testing)

### Best Practices

1. **Selective Usage:**
   ```php
   // Use getCachedTranslation for frequently accessed content
   $product->getCachedTranslation('name');
   
   // Use filterTranslation for rarely accessed content
   $product->filterTranslation('internal_note');
   ```

2. **Batch Operations:**
   ```php
   // Efficient - single cache operation
   $model->setTranslation('title', 'New Title', 'en');
   
   // Less efficient - multiple cache operations
   foreach($titles as $locale => $title) {
       $model->setTranslation('title', $title, $locale);
   }
   ```

3. **Cache Duration:**
   - Set shorter durations for frequently updated content
   - Set longer durations for static content
   ```php
   // In config/laratrans.php
   'cache' => [
       'duration' => [
           'static_content' => 86400, // 24 hours
           'dynamic_content' => 3600, // 1 hour
       ],
   ],
   ```

### Debugging Cache

1. Check if a translation is cached:
```php
$cacheKey = "laratrans_{$model->getTable()}_{$model->id}_en_title";
$exists = Cache::has($cacheKey);
```

2. Clear all translations cache:
```php
Cache::tags('laratrans')->flush();
```

## Validation

LaraTrans now includes built-in validation:

```php
// Validation will be automatic based on your config
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

## Automatic Features

LaraTrans automatically:
- Validates translations based on your configuration
- Creates translations during model creation
- Updates translations during model updates
- Deletes translations when the model is deleted
- Falls back to the default locale when a translation is missing (configurable)

## Customization

You can customize LaraTrans by:
- Extending the HasTranslations trait
- Modifying the configuration file
- Creating custom validation rules
- Adjusting the migration file

## Testing the Package Locally

To test the package in a local Laravel application:

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

- 2.0.0: Added Laravel 11 support, validation system, and configuration options
- 1.0.4: Initial release with basic translation functionality

## Developed with ❤️ by Mohamad Almoayad.

---

This package is maintained with pride by [Mo Designs Studio](https://www.modesigns.studio).
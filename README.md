<p align="center"><a href="https://www.modesigns.studio" target="_blank"><img src="https://www.modesigns.studio/_next/image?url=%2Fimages%2Flogo%2Fmo-designs-logo.gif&w=256&q=75" width="250" alt="Mo Designs Studio Logo"></a></p>

# LaraTrans

**LaraTrans** is a Laravel package that simplifies the process of handling translations for your models. It allows you to easily create, update, and delete translations for specific model properties, making your application ready for multilingual support with minimal effort.

## Installation

You can install the package via Composer:

```bash
composer require almoayad/laratrans
```

Next, publish the migration file:

```bash
php artisan vendor:publish --provider="Almoayad\LaraTrans\LaraTransServiceProvider" --tag="migrations"
```

Then, run the migration:

```bash
php artisan migrate
```

## Usage

### Step 1: Add the HasTranslations Trait

To start using LaraTrans in your model, simply add the HasTranslations trait to the model:

```bash
use Almoayad\LaraTrans\Traits\HasTranslations;

class SomeModel extends Model
{
    use HasTranslations;
}
```

### Step 2: Create Translations

You can create translations by using the createMany() method on the model's translations() relationship:

```bash
SomeModel::create($request->except('translations'));
# Now let LaraTrans do the rest.
# Translations will be created automatically

# $translations = [
#     ['locale' => 'ar', 'property_name' => 'name', 'value' => 'أسود'],
#     ['locale' => 'en', 'property_name' => 'name', 'value' => 'black'],
#     ['locale' => 'fr', 'property_name' => 'name', 'value' => 'noir'],
# ]
```

### Step 3: Retrieve Translations

To retrieve a translation for a specific property, use the filterTranslation() method:

```bash
$translation = $modelInstance->filterTranslation('name');
```

### Step 4: Automatic Creation, Update, and Deletion

LaraTrans automatically handles the creation, update, and deletion of translations when you create, update, or delete a model record. This ensures that your translations are always in sync with your model data.

## Customization

You can customize the behavior of LaraTrans by extending the HasTranslations trait or by modifying the provided migration file to suit your needs.

## License

LaraTrans is open-source software licensed under the MIT license.

## Feedback and Contributions

If you have any feedback or suggestions, feel free to open an issue or submit a pull request. Contributions are more than welcome!

## Developed with ❤️ by Mohamad Almoayad.

### **Testing the Package Locally**

To test the package in a local Laravel application, you can use `composer`'s `path` repository feature:

1. Add the following to your Laravel application's `composer.json`:

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

This allows you to test the package locally before publishing it.

### **Publishing the Package**

Once everything is working, you can publish your package on [Packagist](https://packagist.org/) by creating an account and submitting your package's GitHub repository URL.

---

This step-by-step guide should make it easier for you to package, test, and publish `Almoayad\LaraTrans`. The updated `README.md` file now reflects these instructions clearly.

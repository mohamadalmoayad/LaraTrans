<?php

namespace Almoayad\LaraTrans\Traits;

use Almoayad\LaraTrans\Models\Translation;
use Almoayad\LaraTrans\Support\TranslationStrategyFactory;
use Almoayad\LaraTrans\Strategies\TranslationStrategy;
use Almoayad\LaraTrans\Validation\TranslationValidator;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    protected ?TranslationValidator $validator = null;
    protected ?TranslationStrategy $translationStrategy = null;

    protected static function bootHasTranslations()
    {
        static::creating(function ($model) {
            if (request()->has('translations')) {
                $model->validateTranslations(request()->input());
            }
        });

        static::created(function ($model) {
            $model->createTranslations();
        });

        static::updating(function ($model) {
            if (request()->has('translations')) {
                $model->validateTranslations(request()->input(), false);
                $model->updateTranslations();
            }
        });

        static::deleting(function ($model) {
            $model->deleteTranslations();
        });

        // Add debug logging
        \Log::debug('Model events registered', [
            'class' => static::class,
            'translations' => request()->input('translations')
        ]);
    }

    protected function getStrategy(): TranslationStrategy
    {
        return $this->translationStrategy ??= TranslationStrategyFactory::make($this);
    }

    public function getTranslationStrategy(): TranslationStrategy
    {
        return $this->getStrategy();
    }

    public function checkTranslationExists(string $property, string $locale): bool
    {
        return $this->getTranslationStrategy()->getTranslation($property, $locale) !== null;
    }

    protected function getValidator(): TranslationValidator
    {
        return $this->validator ??= new TranslationValidator();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function filterTranslation(string $property, string $locale = null): ?string
    {
        $locale = $locale ?: App::getLocale();
        $translation = $this->getStrategy()->getTranslation($property, $locale);

        if (!$translation && config('laratrans.locales.auto_fallback', true)) {
            $fallbackLocale = config('laratrans.locales.fallback_locale');
            $translation = $this->getStrategy()->getTranslation($property, $fallbackLocale);
        }

        return $translation;
    }

    /**
     * Alias method for backward compatibility (plural)
     */
    public function filterTranslations(string $property, string $locale = null): ?string
    {
        return $this->filterTranslation($property, $locale);
    }

    public function setTranslation(string $property, string $value, string $locale = null): void
    {
        $locale = $locale ?: App::getLocale();

        $this->validateTranslations([
            'translations' => [
                [
                    'locale' => $locale,
                    'property_name' => $property,
                    'value' => $value,
                ]
            ]
        ], false);

        $this->getStrategy()->setTranslation($property, $value, $locale);
    }

    protected function createTranslations(): void
    {
        if (request()->has('translations')) {
            try {
                // Validate translations first
                $this->validateTranslations(request()->all());
                // Get validated data and ensure required fields
                $translations = collect(request()->input('translations'))
                    ->map(function ($translation) {
                        return [
                            'locale' => $translation['locale'],
                            'property_name' => $translation['property_name'],
                            'value' => $translation['value'],
                            'translatable_id' => $this->id,
                            'translatable_type' => get_class($this)
                        ];
                    })
                    ->toArray();

                $this->getStrategy()->createTranslations($translations);
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    "Failed to create translations: " . $e->getMessage()
                );
            }
        }
    }

    protected function updateTranslations(): void
    {
        if (request()->has('translations')) {
            foreach (request()->input('translations') as $translation) {
                $this->setTranslation(
                    $translation['property_name'],
                    $translation['value'],
                    $translation['locale']
                );
            }
        }
    }

    protected function deleteTranslations(): void
    {
        $this->getStrategy()->deleteTranslations();
    }

    protected function validateTranslations(array $data, bool $validateUnique = true): void
    {
        $this->getValidator()
            ->setModel($this)
            ->validate($data, $validateUnique);
    }
}
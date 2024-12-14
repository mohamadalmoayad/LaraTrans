<?php

namespace Almoayad\LaraTrans\Traits;

 use Almoayad\LaraTrans\Support\TranslationStrategyFactory;
use Almoayad\LaraTrans\Strategies\TranslationStrategy;
use Almoayad\LaraTrans\Validation\TranslationValidator;
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

        static::updating(function ($model) {
            if (request()->has('translations')) {
                $model->validateTranslations(request()->input());
                $model->updateTranslations();
            }
        });

        static::created(function ($model) {
            $model->createTranslations();
        });

        static::deleting(function ($model) {
            $model->deleteTranslations();
        });
    }

    protected function getStrategy(): TranslationStrategy
    {
        return $this->translationStrategy ??= TranslationStrategyFactory::make($this);
    }

    protected function getValidator(): TranslationValidator
    {
        return $this->validator ??= new TranslationValidator();
    }

    public function translations()
    {
        return $this->getStrategy()->getTranslationsQuery();
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
        ]);

        $this->getStrategy()->setTranslation($property, $value, $locale);
    }

    protected function createTranslations(): void
    {
        if (request()->has('translations')) {
            $this->getStrategy()->createTranslations(request()->input('translations'));
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

    protected function validateTranslations(array $data): void
    {
        $this->getValidator()->validate($data);
    }
}
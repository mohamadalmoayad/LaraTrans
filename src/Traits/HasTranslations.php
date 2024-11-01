<?php

namespace Almoayad\LaraTrans\Traits;

use Almoayad\LaraTrans\Models\Polymorphic\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Almoayad\LaraTrans\Validation\TranslationValidator;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    protected ?TranslationValidator $validator = null;

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
            }
        });

        static::created(function ($model) {
            $model->createTranslations();
        });

        static::updating(function ($model) {
            $model->updateTranslations();
        });

        static::deleting(function ($model) {
            $model->deleteTranslations();
        });
    }

    protected function getValidator(): TranslationValidator
    {
        if (!$this->validator) {
            $this->validator = new TranslationValidator();
        }
        return $this->validator;
    }

    protected function validateTranslations(array $data): void
    {
        $this->getValidator()->validate($data);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function localeTranslation(string $locale = null): MorphMany
    {
        $locale = $locale ?: App::getLocale();
        return $this->morphMany(Translation::class, 'translatable')->where('locale', $locale);
    }

    public function filterTranslation(string $property, string $locale = null): ?string
    {
        $locale = $locale ?: App::getLocale();
        $translation = $this->localeTranslation($locale)->where('property_name', $property)->first();

        if (!$translation && config('laratrans.locales.auto_fallback', true)) {
            $fallbackLocale = config('laratrans.locales.fallback_locale');
            $translation = $this->localeTranslation($fallbackLocale)
                ->where('property_name', $property)
                ->first();
        }

        return $translation?->value;
    }

    public function setTranslation(string $property, string $value, string $locale = null): void
    {
        $locale = $locale ?: App::getLocale();

        // Validate single translation
        $this->validateTranslations([
            'translations' => [
                [
                    'locale' => $locale,
                    'property_name' => $property,
                    'value' => $value,
                ]
            ]
        ]);

        $this->translations()->updateOrCreate(
            ['translatable_id' => $this->id, 'property_name' => $property, 'locale' => $locale],
            ['value' => $value]
        );
    }

    protected function createTranslations(): void
    {
        if (request()->has('translations')) {
            $this->translations()->createMany(request()->input('translations'));
        }
    }

    protected function updateTranslations(): void
    {
        if (request()->has('translations')) {
            foreach (request()->input('translations') as $translation) {
                $this->setTranslation($translation['property_name'], $translation['value'], $translation['locale']);
            }
        }
    }

    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    public function scopeWithTranslation($query, $property, $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        return $query->addSelect([
            'translation' => Translation::select('value')
                ->whereColumn('translatable_id', $this->getTable() . '.id')
                ->where('translatable_type', get_class($this))
                ->where('property_name', $property)
                ->where('locale', $locale)
                ->limit(1)
        ])->withCasts(['translation' => 'string']);
    }

    public function scopeWhereTranslation($query, $property, $value, $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        return $query->whereHas('translations', function ($q) use ($property, $value, $locale) {
            $q->where('property_name', $property)
                ->where('locale', $locale)
                ->where('value', 'like', "%$value%");
        });
    }
}
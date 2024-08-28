<?php

namespace Almoayad\LaraTrans\Traits;

use Almoayad\LaraTrans\Models\Polymorphic\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    protected static function bootHasTranslations()
    {
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
        return $translation ? $translation->value : null;
    }

    public function setTranslation(string $property, string $value, string $locale = null): void
    {
        $locale = $locale ?: App::getLocale();
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
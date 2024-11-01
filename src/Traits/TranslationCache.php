<?php
namespace Almoayad\LaraTrans\Traits;

use Illuminate\Support\Facades\Cache;

trait TranslationCache
{
    protected function getCacheKey(string $locale, string $property): string
    {
        return "translation_{$this->getTable()}_{$this->id}_{$locale}_{$property}";
    }

    public function getCachedTranslation(string $property, string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $cacheKey = $this->getCacheKey($locale, $property);

        return Cache::remember($cacheKey, config('laratrans.cache_duration', 3600), function () use ($property, $locale) {
            return $this->filterTranslation($property, $locale);
        });
    }

    public function setTranslation(string $property, string $value, string $locale = null): void
    {
        $locale = $locale ?: app()->getLocale();
        parent::setTranslation($property, $value, $locale);

        // Clear the cache for this translation
        Cache::forget($this->getCacheKey($locale, $property));
    }

    protected function deleteTranslations(): void
    {
        // Clear all cached translations for this model
        $this->translations->each(function ($translation) {
            Cache::forget($this->getCacheKey($translation->locale, $translation->property_name));
        });

        parent::deleteTranslations();
    }
}
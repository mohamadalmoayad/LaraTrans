<?php

namespace Almoayad\LaraTrans\Strategies;

use Almoayad\LaraTrans\Models\Translation;

class SingleTableStrategy extends TranslationStrategy
{
    public function getTranslation(string $property, string $locale): ?string
    {
        return $this->model->translations()
            ->where('property_name', $property)
            ->where('locale', $locale)
            ->value('value');
    }

    public function setTranslation(string $property, string $value, string $locale): void
    {
        $this->model->translations()->updateOrCreate(
            [
                'property_name' => $property,
                'locale' => $locale
            ],
            ['value' => $value]
        );
    }

    public function deleteTranslations(): void
    {
        $this->model->translations()->delete();
    }

    public function getTranslationsQuery(string $locale = null)
    {
        $query = $this->model->translations();
        return $locale ? $query->where('locale', $locale) : $query;
    }

    public function createTranslations(array $translations): void
    {
        $this->model->translations()->createMany($translations);
    }

    public function getTableName(): string
    {
        return config('laratrans.table_name', 'laratrans_translations');
    }
}
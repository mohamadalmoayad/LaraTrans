<?php

namespace Almoayad\LaraTrans\Strategies;

class DedicatedTableStrategy extends TranslationStrategy
{
    public function getTranslation(string $property, string $locale): ?string
    {
        return $this->model->translationsTable()
            ->where('property_name', $property)
            ->where('locale', $locale)
            ->value('value');
    }

    public function setTranslation(string $property, string $value, string $locale): void
    {
        $this->model->translationsTable()->updateOrCreate(
            [
                'model_id' => $this->model->getKey(),
                'property_name' => $property,
                'locale' => $locale
            ],
            ['value' => $value]
        );
    }

    public function deleteTranslations(): void
    {
        $this->model->translationsTable()
            ->where('model_id', $this->model->getKey())
            ->delete();
    }

    public function getTranslationsQuery(string $locale = null)
    {
        $query = $this->model->translationsTable();
        return $locale ? $query->where('locale', $locale) : $query;
    }

    public function createTranslations(array $translations): void
    {
        $mappedTranslations = collect($translations)->map(function ($translation) {
            return array_merge($translation, ['model_id' => $this->model->getKey()]);
        })->toArray();

        $this->model->translationsTable()->insert($mappedTranslations);
    }

    public function getTableName(): string
    {
        return config('laratrans.storage.table_prefix', 'trans_') .
            $this->model->getTable() .
            '_translations';
    }
}
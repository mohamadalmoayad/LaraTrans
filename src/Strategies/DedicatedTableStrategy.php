<?php

namespace Almoayad\LaraTrans\Strategies;

class DedicatedTableStrategy extends TranslationStrategy
{
    protected function getTranslationModel(): DedicatedTranslation
    {
        $model = new DedicatedTranslation([], $this->model);
        $model->setTable(DedicatedTranslation::getModelTableName($this->model));
        return $model;
    }

    public function getTranslation(string $property, string $locale): ?string
    {
        return $this->getTranslationModel()
            ->where('model_id', $this->model->getKey())
            ->where('property_name', $property)
            ->where('locale', $locale)
            ->value('value');
    }

    public function setTranslation(string $property, string $value, string $locale): void
    {

        $this->getTranslationModel()->updateOrInsert(
            [
                'model_id' => $this->model->getKey(),
                'property_name' => $property,
                'locale' => $locale
            ],
            [
                'value' => $value,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
    }

    public function deleteTranslations(): void
    {

        $this->getTranslationModel()
            ->where('model_id', $this->model->getKey())
            ->delete();
    }

    public function getTranslationsQuery(string $locale = null)
    {

        $query = $this->getTranslationModel()
            ->where('model_id', $this->model->getKey());

        return $locale ? $query->where('locale', $locale) : $query;
    }

    public function createTranslations(array $translations): void
    {

        $mappedTranslations = collect($translations)->map(function ($translation) {
            return array_merge($translation, ['model_id' => $this->model->getKey()]);
        })->toArray();

        $this->getTranslationModel()->insert($mappedTranslations);
    }

    public function getTableName(): string
    {
        return config('laratrans.storage.table_prefix', 'trans_') .
            $this->model->getTable() .
            '_translations';
    }
}
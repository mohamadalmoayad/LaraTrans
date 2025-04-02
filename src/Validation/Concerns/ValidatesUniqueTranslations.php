<?php

namespace Almoayad\LaraTrans\Validation\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait ValidatesUniqueTranslations
{
    protected function validateUniqueTranslations(array $data): void
    {
        if (!$this->model) {
            throw new \RuntimeException('Model must be set before validation');
        }

        $translations = collect($data['translations'] ?? []);
        $strategy = $this->model->getTranslationStrategy();

        $this->model->exists
            ? $this->validateExistingTranslations($strategy, $translations)
            : $this->validateNewTranslations($strategy, $translations);
    }

    protected function validateNewTranslations($strategy, $translations): void
    {
        foreach ($translations as $translation) {
            $exists = match (config('laratrans.storage.mode')) {
                'single_table' => $this->checkNewSingleTableTranslation($strategy, $translation),
                'dedicated_tables' => $this->checkNewDedicatedTableTranslation($strategy, $translation),
            };

            if ($exists) {
                $this->throwDuplicateError($translation);
            }
        }
    }

    protected function validateExistingTranslations($strategy, $translations): void
    {
        foreach ($translations as $translation) {
            $exists = match (config('laratrans.storage.mode')) {
                'single_table' => $this->checkExistingSingleTableTranslation($strategy, $translation),
                'dedicated_tables' => $this->checkExistingDedicatedTableTranslation($strategy, $translation),
            };

            if ($exists) {
                $this->throwDuplicateError($translation);
            }
        }
    }

    protected function checkNewSingleTableTranslation($strategy, array $translation): bool
    {
        return DB::table($strategy->getTableName())
            ->where('translatable_type', get_class($this->model))
            ->where('property_name', $translation['property_name'])
            ->where('locale', $translation['locale'])
            ->exists();
    }

    protected function checkExistingSingleTableTranslation($strategy, array $translation): bool
    {
        return DB::table($strategy->getTableName())
            ->where('translatable_type', get_class($this->model))
            ->where('translatable_id', '!=', $this->model->getKey())
            ->where('property_name', $translation['property_name'])
            ->where('locale', $translation['locale'])
            ->exists();
    }

    protected function checkNewDedicatedTableTranslation($strategy, array $translation): bool
    {
        return DB::table($strategy->getTableName())
            ->where('property_name', $translation['property_name'])
            ->where('locale', $translation['locale'])
            ->exists();
    }

    protected function checkExistingDedicatedTableTranslation($strategy, array $translation): bool
    {
        return DB::table($strategy->getTableName())
            ->where('model_id', '!=', $this->model->getKey())
            ->where('property_name', $translation['property_name'])
            ->where('locale', $translation['locale'])
            ->exists();
    }

    protected function throwDuplicateError(array $translation): void
    {
        throw ValidationException::withMessages([
            'translations' => [
                "Translation already exists for property '{$translation['property_name']}' with locale '{$translation['locale']}'"
            ]
        ]);
    }
}
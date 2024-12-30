<?php

namespace Almoayad\LaraTrans\Strategies;

use Illuminate\Database\Eloquent\Model;

abstract class TranslationStrategy
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    abstract public function getTranslation(string $property, string $locale): ?string;
    abstract public function setTranslation(string $property, string $value, string $locale): void;
    abstract public function deleteTranslations(): void;
    abstract public function getTranslationsQuery(string $locale = null);
    abstract public function createTranslations(array $translations): void;
    abstract public function getTableName(): string;
}
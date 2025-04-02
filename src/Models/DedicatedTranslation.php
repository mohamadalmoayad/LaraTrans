<?php

namespace Almoayad\LaraTrans\Models;

use Illuminate\Database\Eloquent\Model;

class DedicatedTranslation extends BaseTranslation
{
    protected ?Model $parentModel = null;

    public function __construct(array $attributes = [], ?Model $parentModel = null)
    {
        parent::__construct($attributes);
        $this->parentModel = $parentModel;
        if ($parentModel) {
            $this->table = $this->getTranslationTableName();
        }
    }

    protected function getTranslationTableName(): string
    {
        if (!$this->parentModel) {
            throw new \RuntimeException('Parent model required for table operations');
        }

        return config('laratrans.storage.table_prefix', 'trans_') .
            $this->parentModel->getTable() .
            '_translations';
    }

    public static function getModelTableName(Model $model): string
    {
        return config('laratrans.storage.table_prefix', 'trans_') .
            $model->getTable() .
            '_translations';
    }
}
<?php

namespace Almoayad\LaraTrans\Models;

use Illuminate\Database\Eloquent\Model;

class DedicatedTranslation extends BaseTranslation
{
    protected ?Model $parentModel = null;
    protected bool $validationOnly = false;

    public function __construct(array $attributes = [], ?Model $parentModel = null)
    {
        parent::__construct($attributes);
        
        $this->parentModel = $parentModel;
        if (!$parentModel) {
            $this->validationOnly = true;
        }
        
        if (!$this->validationOnly) {
            $this->table = $this->getTranslationTableName();
        }
    }

    protected function getTranslationTableName(): string
    {
        if ($this->validationOnly) {
            return config('laratrans.storage.table_name', 'laratrans_translations');
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
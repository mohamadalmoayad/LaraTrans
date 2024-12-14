<?php

namespace Almoayad\LaraTrans\Models;

use Illuminate\Database\Eloquent\Model;

class DedicatedTranslation extends BaseTranslation
{
    protected Model $parentModel;

    public function __construct(array $attributes = [], Model $parentModel = null)
    {
        parent::__construct($attributes);
        $this->parentModel = $parentModel;
        $this->table = $this->getTranslationTableName();
    }

    protected function getTranslationTableName(): string
    {
        return config('laratrans.storage.table_prefix', 'trans_') .
            $this->parentModel->getTable() .
            '_translations';
    }
}
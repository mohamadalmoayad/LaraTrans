<?php

namespace Almoayad\LaraTrans\Models;

class Translation extends BaseTranslation
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laratrans.storage.table_name', 'laratrans_translations');
    }

    public function translatable()
    {
        return $this->morphTo();
    }
}
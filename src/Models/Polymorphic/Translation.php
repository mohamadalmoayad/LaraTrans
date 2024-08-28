<?php

namespace Almoayad\LaraTrans\Models\Polymorphic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'property_name', 'value', 'translatable_id', 'translatable_type'];
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}

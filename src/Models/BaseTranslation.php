<?php

namespace Almoayad\LaraTrans\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseTranslation extends Model
{
    protected $fillable = ['locale', 'property_name', 'value'];

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'in:' . implode(',', config('laratrans.locales.supported'))],
            'property_name' => ['required', 'string'],
            'value' => [
                'required',
                'string',
                'min:' . config('laratrans.validation.default_rules.min', 1),
                'max:' . config('laratrans.validation.default_rules.max', 255),
            ],
        ];
    }
}
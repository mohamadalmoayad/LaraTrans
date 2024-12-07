<?php

namespace Almoayad\LaraTrans\Support;

use Almoayad\LaraTrans\Strategies\SingleTableStrategy;
use Almoayad\LaraTrans\Strategies\DedicatedTableStrategy;
use Almoayad\LaraTrans\Strategies\TranslationStrategy;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TranslationStrategyFactory
{
    public static function make(Model $model): TranslationStrategy
    {
        $mode = config('laratrans.storage.mode', 'single_table');

        return match ($mode) {
            'single_table' => new SingleTableStrategy($model),
            'dedicated_tables' => new DedicatedTableStrategy($model),
            default => throw new InvalidArgumentException("Invalid translation storage mode: {$mode}")
        };
    }
}
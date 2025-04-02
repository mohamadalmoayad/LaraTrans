<?php

namespace Almoayad\LaraTrans\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CreateTranslationTableCommand extends Command
{
    protected $signature = 'laratrans:table {model}';
    protected $description = 'Create a translation table for the given model';

    public function handle()
    {
        $modelName = $this->argument('model');
        $modelTable = Str::snake(Str::pluralStudly($modelName));
        $tableName = config('laratrans.storage.table_prefix', 'trans_') . $modelTable . '_translations';

        $this->info($modelName);
        $this->info($tableName);
        $this->createMigration($tableName, $modelTable);
        $this->info("Created migration for table: {$tableName}");
        return 0;
    }

    protected function createMigration(string $tableName, string $modelTable): void
    {
        $stub = File::get(__DIR__ . '/../stubs/migration.stub');
        $migration = str_replace(
            ['{{table}}', '{{modelTable}}'],
            [$tableName, $modelTable],
            $stub
        );

        $filename = date('Y_m_d_His') . "_create_{$tableName}_table.php";
        File::put(database_path("migrations/{$filename}"), $migration);
    }
}
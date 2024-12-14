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
        $tableName = config('laratrans.storage.table_prefix') .
            Str::snake(Str::pluralStudly($modelName)) .
            '_translations';

        $this->createMigration($tableName);

        $this->info("Created migration for table: {$tableName}");
        return 0;
    }

    protected function createMigration(string $tableName): void
    {
        $stub = File::get(__DIR__ . '/../stubs/migration.stub');
        $migration = str_replace('{{table}}', $tableName, $stub);

        $filename = date('Y_m_d_His') . "_create_{$tableName}_table.php";
        File::put(database_path("migrations/{$filename}"), $migration);
    }
}
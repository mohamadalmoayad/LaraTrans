<?php

namespace Almoayad\LaraTrans\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class MigrateTranslationStrategyCommand extends Command
{
    protected $signature = 'laratrans:migrate-strategy 
                          {--reverse : Migrate from dedicated tables back to single table}
                          {--cleanup : Clean up source tables after migration (not recommended)}
                          {--force : Force cleanup without confirmation}';
    protected $description = 'Migrate translations between storage strategies';

    public function handle()
    {
        if ($this->option('cleanup')) {
            $this->line('');
            $this->warn('⚠️  IMPORTANT WARNINGS:');
            $this->warn('--------------------------------');
            $this->warn('1. It is strongly recommended to backup your database first:');
            $this->warn('   mysqldump -u your_user -p your_database > backup.sql');
            $this->warn('');
            $this->warn('2. Using --cleanup flag will immediately remove data from source tables.');
            $this->warn('3. It is recommended to run cleanup separately after verifying migration:');
            $this->warn('   php artisan laratrans:cleanup --dedicated|--single');
            $this->line('');

            if (!$this->confirm('Do you want to continue with automatic cleanup?')) {
                return 1;
            }
        }

        $result = $this->option('reverse')
            ? $this->migrateToSingleTable()
            : $this->migrateToDedicatedTables();

        if ($result === 0 && $this->option('cleanup')) {
            $exitCode = Artisan::call('laratrans:cleanup', [
                '--' . ($this->option('reverse') ? 'dedicated' : 'single') => true,
                '--force' => true, // Always force since user already confirmed
            ]);

            $this->info(trim(Artisan::output()));
            return $exitCode;
        }

        return $result;
    }

    protected function migrateToDedicatedTables(): int
    {
        $this->info('Starting migration to dedicated tables...');

        $singleTableName = config('laratrans.storage.table_name', 'laratrans_translations');
        echo $singleTableName;
        $groupedTranslations = DB::table($singleTableName)
            ->select('translatable_type', DB::raw('COUNT(*) as count'))
            ->groupBy('translatable_type')
            ->get();


        foreach ($groupedTranslations as $group) {
            $modelName = class_basename($group->translatable_type);
            $tableName = config('laratrans.storage.table_prefix') .
                Str::snake(Str::pluralStudly($modelName)) .
                '_translations';
            echo $tableName;

            // Create migration if table doesn't exist
            if (!Schema::hasTable($tableName)) {
                $this->call('laratrans:table', ['model' => $modelName]);
                $this->call('migrate');
            }


            $bar = $this->output->createProgressBar($group->count);

            DB::table($singleTableName)
                ->where('translatable_type', $group->translatable_type)
                ->chunkById(100, function ($translations) use ($tableName, $bar) {
                    $data = $translations->map(function ($translation) {
                        return [
                            'model_id' => $translation->translatable_id,
                            'property_name' => $translation->property_name,
                            'locale' => $translation->locale,
                            'value' => $translation->value,
                            'created_at' => $translation->created_at,
                            'updated_at' => $translation->updated_at,
                        ];
                    })->toArray();

                    DB::table($tableName)->insert($data);
                    $bar->advance(count($translations));
                });

            $bar->finish();
            $this->info("\nMigrated {$group->translatable_type} translations");
        }

        $this->info('Migration completed successfully!');
        return 0;
    }

    protected function migrateToSingleTable(): int
    {
        $this->info('Starting migration to single table...');

        // Get single table name
        $singleTableName = config('laratrans.storage.table_name', 'laratrans_translations');

        // Find all dedicated translation tables
        $prefix = config('laratrans.storage.table_prefix', 'trans_');
        $tables = collect(DB::select("SHOW TABLES LIKE '{$prefix}%_translations'"))
            ->map(fn($table) => (array)$table)
            ->map(fn($table) => reset($table))
            ->values()
            ->toArray();

        if (empty($tables)) {
            $this->warn('No dedicated translation tables found.');
            return 0;
        }

        $totalRecords = 0;
        foreach ($tables as $table) {
            // Extract model name from table name
            $modelName = str_replace([$prefix, '_translations'], '', $table);
            $modelTable = Str::plural(Str::snake($modelName));
            $this->info($modelName);
            $this->info($modelTable);
            $count = DB::table($table)->count();
            $totalRecords += $count;

            $this->info("Migrating {$count} translations from {$table}...");
            $bar = $this->output->createProgressBar($count);

            DB::table($table)->chunkById(100, function ($translations) use ($singleTableName, $modelTable, $bar) {
                $data = collect($translations)->map(function ($translation) use ($modelTable) {
                    return [
                        'translatable_type' => $this->getModelClass($modelTable),
                        'translatable_id' => $translation->model_id,
                        'property_name' => $translation->property_name,
                        'locale' => $translation->locale,
                        'value' => $translation->value,
                        'created_at' => $translation->created_at,
                        'updated_at' => $translation->updated_at,
                    ];
                })->toArray();

                try {
                    DB::table($singleTableName)->insert($data);
                    $bar->advance(count($translations));
                } catch (\Exception $e) {
                    $this->error("Error migrating data from {$modelTable}: " . $e->getMessage());
                    return false;
                }
            });

            $bar->finish();
            $this->info("\nCompleted migration from {$table}");
        }

        $this->info("\nSuccessfully migrated {$totalRecords} translations to single table strategy");
        return 0;
    }

    /**
     * Get fully qualified model class name from table name
     */
    protected function getModelClass(string $table): string
    {
        // Convert table name to model name (posts -> Post)
        $modelName = Str::studly(Str::singular($table));

        // Check common model locations
        $possibleNamespaces = [
            "\\App\\Models\\{$modelName}",
            "\\App\\{$modelName}"
        ];

        foreach ($possibleNamespaces as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        // Default to App\Models namespace
        return "\\App\\Models\\{$modelName}";
    }
}
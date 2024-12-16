<?php

namespace Almoayad\LaraTrans\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateTranslationStrategyCommand extends Command
{
    protected $signature = 'laratrans:migrate-strategy 
                          {--reverse : Migrate from dedicated tables back to single table}';
    protected $description = 'Migrate translations between storage strategies';

    public function handle()
    {
        if ($this->option('reverse')) {
            return $this->migrateToSingleTable();
        }

        return $this->migrateToDedicatedTables();
    }

    protected function migrateToDedicatedTables(): int
    {
        $this->info('Starting migration to dedicated tables...');
        $singleTableName = config('laratrans.storage.table_name', 'laratrans_translations');
        $groupedTranslations = DB::table($singleTableName)
            ->select('translatable_type', DB::raw('COUNT(*) as count'))
            ->groupBy('translatable_type')
            ->get();

        foreach ($groupedTranslations as $group) {
            $model = new $group->translatable_type;
            $tableName = config('laratrans.storage.table_prefix') . $model->getTable() . '_translations';

            if (!Schema::hasTable($tableName)) {
                $this->call('laratrans:table', ['model' => class_basename($group->translatable_type)]);
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
}
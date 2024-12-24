<?php

namespace Almoayad\LaraTrans\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupTranslationsCommand extends Command
{
    protected $signature = 'laratrans:cleanup 
        {--dedicated : Truncate dedicated translation tables}
        {--single : Truncate single translation table}
        {--force : Force cleanup without confirmation}';

    protected $description = 'Clean up data from translation tables after migration';

    public function handle()
    {
        if (!$this->option('dedicated') && !$this->option('single')) {
            $this->error('Please specify which tables to clean up (--dedicated or --single)');
            return 1;
        }

        if ($this->option('dedicated')) {
            return $this->cleanupDedicatedTables();
        }

        return $this->cleanupSingleTable();
    }

    protected function cleanupDedicatedTables(): int
    {
        if (
            !$this->option('force') &&
            !$this->confirm('Are you sure you want to truncate all dedicated translation tables? This will remove all data but keep the tables.')
        ) {
            return 1;
        }

        $prefix = config('laratrans.storage.table_prefix', 'trans_');
        $tables = collect(DB::select("SHOW TABLES LIKE '{$prefix}%_translations'"))
            ->map(fn($table) => (array)$table)
            ->map(fn($table) => reset($table));

        if ($tables->isEmpty()) {
            $this->warn('No dedicated translation tables found.');
            return 0;
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info("Truncated table: {$table}");
            }
        }

        return 0;
    }

    protected function cleanupSingleTable(): int
    {
        if (
            !$this->option('force') &&
            !$this->confirm('Are you sure you want to truncate the single translation table? This will remove all data but keep the table.')
        ) {
            return 1;
        }

        $tableName = config('laratrans.storage.table_name', 'laratrans_translations');

        if (!Schema::hasTable($tableName)) {
            $this->warn('Single translation table not found.');
            return 0;
        }

        DB::table($tableName)->truncate();
        $this->info("Truncated single translation table: {$tableName}");

        return 0;
    }
}
<?php

namespace Almoayad\LaraTrans;

use Illuminate\Support\ServiceProvider;

class LaraTransServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the config first
        $this->mergeConfigFrom(
            __DIR__ . '/Config/laratrans.php',
            'laratrans'
        );
    }

    public function boot()
    {
        // Publish config before migrations
        $this->publishes([
            __DIR__ . '/Config/laratrans.php' => config_path('laratrans.php'),
        ], 'config');

        // Publish migration after config
        $this->publishes([
            __DIR__ . '/Database/Migrations/create_laratrans_translations_table.php' =>
                $this->getMigrationFileName(),
        ], 'migrations');
    }

    /**
     * Returns the migration file name with timestamp
     */
    protected function getMigrationFileName(): string
    {
        $timestamp = date('Y_m_d_His');
        $tableName = config('laratrans.table_name', 'laratrans_translations');

        return database_path(
            "migrations/{$timestamp}_create_{$tableName}_table.php"
        );
    }
}
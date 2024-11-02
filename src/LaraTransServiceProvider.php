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

        // Publish migration
        $this->publishes([
            __DIR__ . '/Database/Migrations/create_LaraTrans_translations_table.php' => $this->getMigrationFileName(),
            ,
        ], 'migrations');

        // Publish config
        $this->publishes([
            __DIR__ . '/Config/laratrans.php' => config_path('laratrans.php'),
        ], 'config');

        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/Config/laratrans.php',
            'laratrans'
        );

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

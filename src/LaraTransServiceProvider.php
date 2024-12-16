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
        $this->registerMigrations();
        $this->registerCommands();
    }

    protected function registerMigrations(): void
    {
        if ($this->shouldUseSingleTable()) {
            $timestamp = date('Y_m_d_His');
            $tableName = config('laratrans.table_name', 'laratrans_translations');
            $name = $timestamp . '_' . $tableName;
            $this->publishes([
                __DIR__ . '/Database/Migrations/create_laratrans_translations_table.php' =>
                    $this->getMigrationFileName($name),
            ], 'laratrans-migrations');
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateTranslationTableCommand::class,
            ]);
        }
    }

    protected function shouldUseSingleTable(): bool
    {
        return config('laratrans.storage.mode', 'single_table') === 'single_table';
    }

    protected function getMigrationFileName(string $name): string
    {
        return database_path("migrations/{$name}.php");
    }
}
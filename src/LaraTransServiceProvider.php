<?php

namespace Almoayad\LaraTrans;

use Illuminate\Support\ServiceProvider;

class LaraTransServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish migration
        $this->publishes([
            __DIR__.'/Database/Migrations/2024_05_09_060318_create_translations_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_translations_table.php'),
        ], 'migrations');

        // Register the translation trait
        $this->registerTranslations();
    }

    public function register()
    {
        //
    }

    protected function registerTranslations()
    {
        // Register the translation functionality
    }
}

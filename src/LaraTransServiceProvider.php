<?php

namespace Almoayad\LaraTrans;

use Illuminate\Support\ServiceProvider;

class LaraTransServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish migration
        $this->publishes([
            __DIR__.'/Database/Migrations/create_LaraTrans_translations_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_LaraTrans_translations_table.php'),
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

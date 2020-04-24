<?php

namespace Statch\Translations;

use Illuminate\Support\ServiceProvider;
use Statch\Translations\Contracts\Translation as TranslationContract;
use Statch\Translations\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class TranslationsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(Filesystem $filesystem)
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
               // Publishing the configuration file.
                $this->publishes([
                    __DIR__.'/../config/statch-translations.php' => config_path('statch-translations.php'),
                ], 'config');

                // Publishing the migration file.
                  $this->publishes([
                    __DIR__.'/../database/migrations/create_statch_translations_table.php.stub' => $this->getMigrationFileName($filesystem),
                ], 'migrations');
        }


        $this->app->bind(TranslationContract::class, config('statch-translations.model'));
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statch-translations.php', 'statch-translations');
    }


    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');
        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_statch_translations_table.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_statch_translations_table.php")
            ->first();
    }
}

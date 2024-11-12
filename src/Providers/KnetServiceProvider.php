<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Console\InstallCommand;
use Asciisd\Knet\Console\KnetCommand;
use Asciisd\Knet\Console\PublishCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KnetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
//        $this->registerLogger();
        $this->registerRoutes();
        $this->registerResources();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->configure();
        $this->registerServices();
        $this->registerCommands();

        if (!class_exists('Knet')) {
            class_alias('Asciisd\Knet\Knet', 'Knet');
        }
    }

    /**
     * Set up the configuration for Cashier.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/knet.php', 'knet'
        );
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix'    => config('knet.path'),
            'namespace' => 'Asciisd\Knet\Http\Controllers',
            'as'        => 'knet.',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    /**
     * Register the package resources.
     */
    protected function registerResources(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'knet');
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/knet.php' => $this->app->configPath('knet.php'),
            ], 'knet-config');

            $this->publishes([
                __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
            ], 'knet-migrations');

            $this->publishes([
                __DIR__.'/../../resources/views' => $this->app->resourcePath('views/vendor/knet'),
            ], 'knet-views');

            $this->publishes([
                __DIR__.'/../../public' => public_path('vendor/knet'),
            ], 'knet-assets');

            $this->publishes([
                __DIR__.'/../../stubs/KnetServiceProvider.stub' => app_path('Providers/KnetServiceProvider.php'),
            ], 'knet-provider');
        }
    }

    /**
     * Register the Horizon Artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                KnetCommand::class,
                InstallCommand::class,
                PublishCommand::class,
            ]);
        }
    }

    public function registerServices()
    {
        //
    }
}

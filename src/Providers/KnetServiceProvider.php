<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Console\KnetCommand;
use Asciisd\Knet\Console\PublishCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Console\InstallCommand;
use Illuminate\Support\Facades\Config;

class KnetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/knet.php', 'knet');
        
        $this->app->singleton(KnetConfig::class, function () {
            return new KnetConfig(Config::get('knet'));
        });

        if (! class_exists('Knet')) {
            class_alias('Asciisd\Knet\Knet', 'Knet');
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => Config::get('knet.path'),
            'namespace' => 'Asciisd\Knet\Http\Controllers',
            'as' => 'knet.',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    public function registerServices()
    {
        //
    }

    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../../config/knet.php' => $this->app->configPath('knet.php'),
        ], 'knet-config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'knet-migrations');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                KnetCommand::class,
                PublishCommand::class,
            ]);
        }
    }
}

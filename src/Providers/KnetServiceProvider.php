<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Console\KnetCommand;
use Asciisd\Knet\Console\PublishCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Console\InstallCommand;

class KnetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
            
            // Register publishable resources
            $this->publishes([
                __DIR__.'/../../config/knet.php' => $this->app->configPath('knet.php'),
            ], 'knet-config');

            $this->publishes([
                __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
            ], 'knet-migrations');
            
            // Register commands
            $this->commands([
                InstallCommand::class,
                KnetCommand::class,
                PublishCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/knet.php', 'knet'
        );
        
        $this->app->singleton(KnetConfig::class, function ($app) {
            return new KnetConfig(config('knet'));
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
            'prefix' => config('knet.path'),
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
}

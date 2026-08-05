<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Cashier\KnetProcessor;
use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Console\InstallCommand;
use Asciisd\Knet\Console\KnetCommand;
use Asciisd\Knet\Console\PublishCommand;
use Asciisd\Knet\Contracts\EncryptsPayload;
use Asciisd\Knet\Contracts\TransactionRepository;
use Asciisd\Knet\Knet;
use Asciisd\Knet\KPayEncryption;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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

        $this->registerCashierDriver();

        $this->app->singleton(KnetConfig::class, function () {
            return new KnetConfig(Config::get('knet'));
        });

        $this->app->bind(EncryptsPayload::class, KPayEncryption::class);
        $this->app->bind(TransactionRepository::class, KnetTransactionRepository::class);

        if (! class_exists('Knet')) {
            class_alias('Asciisd\Knet\Knet', 'Knet');
        }
    }

    /**
     * Register the `knet` driver in cashier-core's driver map so any
     * connection declaring `driver => knet` resolves to the processor. An
     * entry the host already mapped wins.
     */
    protected function registerCashierDriver(): void
    {
        Config::set('cashier-core.drivers', array_merge(
            ['knet' => KnetProcessor::class],
            (array) Config::get('cashier-core.drivers', []),
        ));
    }

    /**
     * Register the package routes, unless Knet::ignoreRoutes() was called.
     */
    protected function registerRoutes(): void
    {
        if (! Knet::$registersRoutes) {
            return;
        }

        Route::group([
            'prefix' => Config::get('knet.path'),
            'namespace' => 'Asciisd\Knet\Http\Controllers',
            'as' => 'knet.',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    protected function registerMigrations()
    {
        if ($this->app->runningInConsole() && Knet::$runsMigrations) {
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

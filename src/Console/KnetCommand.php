<?php

namespace Asciisd\Knet\Console;

use Asciisd\Knet\Config\KnetConfig;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'knet:check')]
class KnetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knet:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Knet configuration and requirements';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(KnetConfig $config): int
    {
        $this->info('Checking Knet configuration...');

        try {
            // Check required configuration
            $this->checkConfiguration($config);

            // Check database migrations
            $this->checkMigrations();

            // Check routes
            $this->checkRoutes();

            $this->newLine();
            $this->info('✓ Everything is configured correctly! You can start processing Knet payments.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function checkConfiguration(KnetConfig $config): void
    {
        $this->components->task('Checking Transport ID', function() use ($config) {
            if (empty($config->getTransportId())) {
                throw new \Exception('Missing KNET_TRANSPORT_ID in environment variables');
            }
            return true;
        });

        $this->components->task('Checking Transport Password', function() use ($config) {
            if (empty($config->getTransportPassword())) {
                throw new \Exception('Missing KNET_TRANSPORT_PASSWORD in environment variables');
            }
            return true;
        });

        $this->components->task('Checking Resource Key', function() use ($config) {
            if (empty($config->getResourceKey())) {
                throw new \Exception('Missing KNET_RESOURCE_KEY in environment variables');
            }
            return true;
        });

        $this->components->task('Checking Debug Mode', function() use ($config) {
            if ($config->isDebugMode() && app()->environment('production')) {
                $this->warn('Warning: Debug mode is enabled in production environment');
            }
            return true;
        });
    }

    private function checkMigrations(): void
    {
        $this->components->task('Checking Database Migrations', function() {
            if (!$this->hasRunMigrations()) {
                throw new \Exception('Knet migrations have not been run. Please run: php artisan migrate');
            }
            return true;
        });
    }

    private function checkRoutes(): void
    {
        $this->components->task('Checking Routes', function() {
            if (!$this->hasRequiredRoutes()) {
                throw new \Exception('Knet routes are not registered. Please check your service provider');
            }
            return true;
        });
    }

    private function hasRunMigrations(): bool
    {
        return \Schema::hasTable('knet_transactions');
    }

    private function hasRequiredRoutes(): bool
    {
        $routes = app('router')->getRoutes();
        return $routes->hasNamedRoute('knet.response.store')
            && $routes->hasNamedRoute('knet.error')
            && $routes->hasNamedRoute('knet.handle');
    }
}

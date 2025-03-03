<?php

namespace Asciisd\Knet\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'knet:publish')]
class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knet:publish 
        {--force : Overwrite any existing files}
        {--config : Only publish the config file}
        {--migrations : Only publish the migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Knet configuration and assets';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Publishing Knet resources...');

        $publishConfig = !$this->option('migrations');
        $publishMigrations = !$this->option('config');

        if ($publishConfig) {
            $this->publishConfiguration();
        }

        if ($publishMigrations) {
            $this->publishMigrations();
        }

        $this->newLine();
        $this->info('Knet resources published successfully!');

        if ($publishConfig) {
            $this->info('Please update your .env file with your Knet credentials:');
            $this->line('  KENT_TRANSPORT_ID=');
            $this->line('  KENT_TRANSPORT_PASSWORD=');
            $this->line('  KENT_RESOURCE_KEY=');
            $this->line('  KNET_DEBUG=true');
        }

        return self::SUCCESS;
    }

    private function publishConfiguration(): void
    {
        $this->components->task('Publishing configuration', function() {
            $params = ['--provider' => 'Asciisd\Knet\Providers\KnetServiceProvider'];
            
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            $this->call('vendor:publish', array_merge(
                $params,
                ['--tag' => 'knet-config']
            ));

            return true;
        });
    }

    private function publishMigrations(): void
    {
        $this->components->task('Publishing migrations', function() {
            $params = ['--provider' => 'Asciisd\Knet\Providers\KnetServiceProvider'];
            
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            $this->call('vendor:publish', array_merge(
                $params,
                ['--tag' => 'knet-migrations']
            ));

            return true;
        });
    }
}

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
            $this->line('  KNET_TRANSPORT_ID=');
            $this->line('  KNET_TRANSPORT_PASSWORD=');
            $this->line('  KNET_RESOURCE_KEY=');
            $this->line('  KNET_DEBUG=true');
        }

        return self::SUCCESS;
    }

    private function publishConfiguration(): void
    {
        $this->publishResource('config', 'Publishing configuration');
    }

    private function publishMigrations(): void
    {
        $this->publishResource('migrations', 'Publishing migrations');
    }

    private function publishResource(string $tag, string $description): void
    {
        $this->components->task($description, function() use ($tag) {
            $params = ['--provider' => 'Asciisd\Knet\Providers\KnetServiceProvider'];
            
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            $this->call('vendor:publish', array_merge(
                $params,
                ['--tag' => "knet-$tag"]
            ));

            return true;
        });
    }
}

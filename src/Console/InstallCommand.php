<?php

namespace Asciisd\Knet\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'knet:install')]
class InstallCommand extends Command
{
    protected $signature = 'knet:install';
    protected $description = 'Install Knet package resources';

    public function handle(): int
    {
        $this->comment('Installing Knet...');
        $this->newLine();

        // Publish configuration
        $this->call('knet:publish', [
            '--force' => true
        ]);

        // Run migrations
        $this->components->task('Running migrations', function() {
            $this->call('migrate');
            return true;
        });

        // Check configuration
        $this->call('knet:check');

        $this->newLine();
        $this->info('Knet has been installed successfully.');
        $this->newLine();
        $this->line('Please review the configuration in config/knet.php');
        $this->line('and update your environment variables in .env');

        return self::SUCCESS;
    }
} 
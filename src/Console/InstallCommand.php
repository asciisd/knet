<?php

namespace Asciisd\Knet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knet:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Knet resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Knet Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'knet-provider']);

        $this->comment('Publishing Knet Views...');
        $this->callSilent('vendor:publish', ['--tag' => 'knet-views']);

        $this->comment('Publishing Knet Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'knet-config']);

        $this->registerKnetServiceProvider();

        $this->info('Knet scaffolding installed successfully.');
    }

    /**
     * Register the Knet service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerKnetServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\KnetServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        {$namespace}\Providers\KnetServiceProvider::class,".PHP_EOL,
            $appConfig
        ));

        file_put_contents(app_path('Providers/KnetServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/KnetServiceProvider.php'))
        ));
    }
}

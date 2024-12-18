<?php

namespace Asciisd\Knet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'knet:install')]
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
    public function handle(): void
    {
        $this->components->info('Installing KNet resources.');

        collect([
            'Service Provider' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'knet-provider']) == 0,
            'Views' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'knet-views']) == 0,
            'Configurations' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'knet-config']) == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->registerKnetServiceProvider();

        $this->components->info('Knet scaffolding installed successfully.');
    }

    /**
     * Register the Knet service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerKnetServiceProvider(): void
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        if (file_exists($this->laravel->bootstrapPath('providers.php'))) {
            ServiceProvider::addProviderToBootstrapFile("{$namespace}\\Providers\\KnetServiceProvider");
        } else {
            $appConfig = file_get_contents(config_path('app.php'));

            if (Str::contains($appConfig, $namespace.'\\Providers\\KnetServiceProvider::class')) {
                return;
            }

            file_put_contents(config_path('app.php'), str_replace(
                "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
                "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        {$namespace}\Providers\KnetServiceProvider::class,".PHP_EOL,
                $appConfig
            ));
        }

        file_put_contents(app_path('Providers/KnetServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/KnetServiceProvider.php'))
        ));
    }
}

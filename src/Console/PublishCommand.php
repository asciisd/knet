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
    protected $signature = 'knet:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the Knet resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'knet-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('view:clear');
    }
}

<?php

namespace Asciisd\Knet\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knet:publish';

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
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'knet-assets',
            '--force' => true,
        ]);
    }
}

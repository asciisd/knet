<?php

namespace Asciisd\Knet\Console;

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
    protected $description = 'Check if everything is ok or not';

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
    public function handle(): void
    {
        if ($this->check_for_transport_id() && $this->check_for_resource_key() && $this->check_for_transport_password()) {
            $this->info('Everything Is OK, you can start receive knet payments');
        }
    }

    private function check_for_transport_id(): bool
    {
        if (config('knet.transport.id') == null) {
            $this->error('Missing TRANSPORT ID');
            return false;
        }

        return true;
    }

    private function check_for_resource_key(): bool
    {
        if (config('knet.resource_key') == null) {
            $this->error('Missing RESOURCE KEY');
            return false;
        }

        return true;
    }

    private function check_for_transport_password(): bool
    {
        if (config('knet.transport.password') == null) {
            $this->error('Missing TRANSPORT PASSWORD');
            return false;
        }

        return true;
    }
}

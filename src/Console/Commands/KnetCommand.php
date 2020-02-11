<?php

namespace Asciisd\Knet\Console\Commands;

use Illuminate\Console\Command;

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
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->check_for_transport_id() && $this->check_for_resource_key() && $this->check_for_transport_password()) {
            $this->info('Everything Is OK, you can start receive knet payments');
        }
    }

    private function check_for_transport_id()
    {
        if (env('KENT_TRANSPORT_ID') == null) {
            $this->error('Missing TRANSPORT ID');
            return false;
        }

        return true;
    }

    private function check_for_transport_password()
    {
        if (env('KENT_TRANSPORT_PASSWORD') == null) {
            $this->error('Missing TRANSPORT PASSWORD');
            return false;
        }

        return true;
    }

    private function check_for_resource_key()
    {
        if (env('KENT_RESOURCE_KEY') == null) {
            $this->error('Missing RESOURCE KEY');
            return false;
        }

        return true;
    }
}

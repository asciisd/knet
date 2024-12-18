<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetTransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public KnetTransaction $transaction)
    {
        //
    }
}

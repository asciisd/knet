<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetReceiptSeen
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param KnetTransaction $transaction
     * @return void
     */
    public function __construct(public KnetTransaction $transaction)
    {
        //
    }
}

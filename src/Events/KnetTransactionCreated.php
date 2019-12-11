<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetTransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * The response payload.
     *
     * @var array
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param KnetTransaction $transaction
     */
    public function __construct(KnetTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}

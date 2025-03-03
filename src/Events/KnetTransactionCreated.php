<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetTransactionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly KnetTransaction $transaction
    ) {}
}

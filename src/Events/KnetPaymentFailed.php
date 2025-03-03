<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class KnetPaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Request $request,
        public readonly ?string $errorMessage = null
    ) {}
}

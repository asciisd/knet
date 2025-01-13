<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\KnetTransaction;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * Create a new IncompletePayment instance.
     */
    public function __construct(public KnetTransaction $transaction, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

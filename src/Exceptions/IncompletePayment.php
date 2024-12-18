<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\Payment;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * Create a new IncompletePayment instance.
     */
    public function __construct(public Payment $payment, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

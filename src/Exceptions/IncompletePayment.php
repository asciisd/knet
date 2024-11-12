<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\Payment;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * The Cashier Payment object.
     */
    public Payment $payment;

    /**
     * Create a new IncompletePayment instance.
     */
    public function __construct(Payment $payment, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->payment = $payment;
    }
}

<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\Payment;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * The Cashier Payment object.
     *
     * @var Payment
     */
    public $payment;

    /**
     * Create a new IncompletePayment instance.
     *
     * @param Payment $payment
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(Payment $payment, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->payment = $payment;
    }
}

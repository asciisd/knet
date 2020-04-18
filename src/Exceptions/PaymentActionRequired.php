<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\Payment;

class PaymentActionRequired extends IncompletePayment
{
    /**
     * Create a new PaymentActionRequired instance.
     *
     * @param Payment $payment
     * @return static
     */
    public static function incomplete(Payment $payment)
    {
        return new static(
            $payment,
            'The payment attempt failed because additional action is required before it can be completed.'
        );
    }
}

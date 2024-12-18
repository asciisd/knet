<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\Payment;

class PaymentFailure extends IncompletePayment
{
    /**
     * Create a new PaymentFailure instance.
     */
    public static function invalidPaymentMethod(Payment $payment): self
    {
        return new static(
            $payment,
            'The payment attempt failed because of an invalid payment method.'
        );
    }
}

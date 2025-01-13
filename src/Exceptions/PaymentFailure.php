<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\KnetTransaction;

class PaymentFailure extends IncompletePayment
{
    /**
     * Create a new PaymentFailure instance.
     */
    public static function invalidPaymentMethod(KnetTransaction $transaction): self
    {
        return new static(
            $transaction,
            'The payment attempt failed because of an invalid payment method.'
        );
    }
}

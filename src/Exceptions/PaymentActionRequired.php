<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\KnetTransaction;

class PaymentActionRequired extends IncompletePayment
{
    /**
     * Create a new PaymentActionRequired instance.
     */
    public static function incomplete(KnetTransaction $transaction): self
    {
        return new static(
            $transaction,
            'The payment attempt failed because additional action is required before it can be completed.'
        );
    }
}

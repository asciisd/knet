<?php

namespace Asciisd\Knet\Exceptions;

use Asciisd\Knet\KnetTransaction;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * The Cashier Payment object.
     *
     * @var KnetTransaction
     */
    public $transaction;

    /**
     * Create a new IncompletePayment instance.
     *
     * @param KnetTransaction $transaction
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(KnetTransaction $transaction, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->transaction = $transaction;
    }
}

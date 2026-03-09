<?php

namespace Asciisd\Knet\Contracts;

use Asciisd\Knet\KnetTransaction;

interface RefundsPayments
{
    public function refundPayment(KnetTransaction $transaction, ?float $amount = null): array;
}

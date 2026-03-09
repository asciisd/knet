<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Services\KnetPaymentService;
use Illuminate\Support\Facades\App;

trait HasKnet
{
    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @throws Exceptions\KnetException
     * @throws Exceptions\PaymentActionRequired
     * @throws Exceptions\PaymentFailure
     */
    public function pay(float $amount, array $options = []): KnetTransaction
    {
        return App::make(KnetPaymentService::class)->createPayment($this, $amount, $options);
    }

    public function knetTransactions(): mixed
    {
        return $this->hasMany(KnetTransaction::class);
    }

    /**
     * @deprecated Use knetTransactions() instead.
     */
    public function knet_transactions(): mixed
    {
        return $this->knetTransactions();
    }
}

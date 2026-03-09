<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\KnetException;
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

    /**
     * Initiate a payment with KFAST faster checkout enabled.
     *
     * The 8-digit customer token is auto-generated from the model's primary key
     * unless overridden via $options['udf3'] or by overriding kfastToken().
     */
    public function payWithKfast(float $amount, array $options = []): KnetTransaction
    {
        $options['udf3'] = $options['udf3'] ?? $this->kfastToken();

        return $this->pay($amount, $options);
    }

    /**
     * Refund a captured transaction (full or partial).
     *
     * @throws KnetException
     */
    public function refund(KnetTransaction $transaction, ?float $amount = null): array
    {
        return App::make(KnetPaymentService::class)->refundPayment($transaction, $amount);
    }

    /**
     * Generate an 8-digit numeric KFAST token for this customer.
     *
     * Override this method on your model if your primary key exceeds 8 digits
     * or you need a different token strategy.
     *
     * @throws KnetException
     */
    public function kfastToken(): string
    {
        $id = $this->getKey();

        if ($id > 99999999) {
            throw new KnetException(
                'User ID exceeds 8 digits for KFAST token. Override kfastToken() on your model.'
            );
        }

        return str_pad((string) $id, 8, '0', STR_PAD_LEFT);
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

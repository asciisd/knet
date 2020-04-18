<?php

namespace Asciisd\Knet;

trait HasKnet
{
    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @param $amount
     * @param array $options
     * @return Payment
     * @throws Exceptions\KnetException
     * @throws Exceptions\PaymentActionRequired
     * @throws Exceptions\PaymentFailure
     */
    public function pay($amount, array $options = [])
    {
        $options['user_id'] = $options['user_id'] ?? $this->id ?? auth()->id();

        $knet = KPayManager::make($amount, $options);

        $payment = new Payment(
            KnetTransaction::create($knet->toArray())
        );

        $payment->validate();

        return $payment;
    }

    /**
     * knet transaction that belong to this payable
     *
     * @return mixed
     */
    public function knet_transactions()
    {
        return $this->hasMany(KnetTransaction::class);
    }
}

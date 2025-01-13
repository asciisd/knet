<?php

namespace Asciisd\Knet;

trait HasKnet
{
    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @throws Exceptions\KnetException
     * @throws Exceptions\PaymentActionRequired
     * @throws Exceptions\PaymentFailure
     */
    public function pay(int $amount, array $options = []): KnetTransaction
    {
        $options['user_id'] = $options['user_id'] ?? $this->id ?? auth()->id();

        $knet = KPayManager::make($amount, $options);

        return KnetTransaction::create($knet->toArray());
    }

    /**
     * knet transaction that belong to this payable
     */
    public function knet_transactions(): mixed
    {
        return $this->hasMany(KnetTransaction::class);
    }
}

<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Facades\Knet as KnetFacade;

/**
 * Trait HasKnet
 * @package Asciisd\Knet
 */
trait HasKnet
{
    public function pay($amount, array $options = [])
    {
        $options['user_id'] = $options['user_id'] ?? $this->id ?? auth()->id();

        $knet = KnetFacade::make($amount, $options);

        return new Payment(
            KnetTransaction::create($knet->toArray())
        );
    }

    public function knet_transactions()
    {
        return $this->hasMany(KnetTransaction::class);
    }
}

<?php

namespace Asciisd\Knet;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Trait HasKnet
 * @package Asciisd\Knet
 */
trait HasKnet
{
    public function pay($amount, array $options = [])
    {
        $options['trackid'] = $options['trackid'] ?? Str::uuid();
        $options['livemode'] = App::environment(['production']);
        $options['result'] = Payment::PENDING;
        $options['amt'] = $amount;
        $options['user_id'] = $this->id;
        $options['url'] = Knet::make($options)->url();

        return new Payment(
            KnetTransaction::create($options)
        );

//        //you can check for validation rules first before you return the payment
//        $payment->validate();
//        return $payment;
    }

    public function knet_transactions()
    {
        return $this->hasMany(KnetTransaction::class);
    }
}

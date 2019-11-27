<?php

namespace Asciisd\Knet;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait HasKnet
{
    public function pay($amount, array $options = [])
    {
        $options = array_merge([
            'trackid' => Str::uuid()
        ], $options);

        $options['livemode'] = App::environment(['production']);
        $options['amt'] = $amount;
        $options['user_id'] = $this->id;
        $options['url'] = (new Knet())
            ->setAmt($amount)
            ->setTrackId($options['trackid'])
            ->url();

        $payment = new Payment(
            KnetTransaction::create($options)
        );

//        $payment->validate();

        return $payment;
    }
}
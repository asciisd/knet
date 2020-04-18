<?php

namespace Asciisd\Knet\Events;

use Asciisd\Knet\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetReceiptSeen
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The response payload.
     *
     * @var Payment
     */
    public $payment;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     * @return void
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }
}

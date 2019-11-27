<?php


namespace Asciisd\Knet\Tests\Unit;


use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Payment;
use Asciisd\Knet\Tests\TestCase;

class PaymentTest extends TestCase
{
    /** @test */
    public function it_can_return_captured() {
        $transaction = new KnetTransaction();
        $transaction->result = Payment::CAPTURED;

        $payment = new Payment($transaction);

        $this->assertTrue($payment->isCaptured());
    }

    /** @test */
    public function it_can_return_not_captured() {
        $transaction = new KnetTransaction();
        $transaction->result = Payment::NOT_CAPTURED;

        $payment = new Payment($transaction);

        $this->assertTrue($payment->isNonCaptured());
    }

    /** @test */
    public function it_can_return_raw_amount() {
        $transaction = new KnetTransaction();
        $transaction->amt = 10;

        $payment = new Payment($transaction);

        $this->assertEquals($payment->rawAmount(), 10);
    }

    /** @test */
    public function it_can_return_formatted_amount() {
        $transaction = new KnetTransaction();
        $transaction->amt = 10;
        $transaction->currency = 'KWD';

        $payment = new Payment($transaction);

        $this->assertEquals($payment->amount(), '10 KWD');
    }
}
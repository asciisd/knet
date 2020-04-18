<?php


namespace Asciisd\Knet\Tests\Unit;


use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Payment;
use Asciisd\Knet\Tests\TestCase;

class PaymentTest extends TestCase
{
    /** @test */
    public function it_can_return_captured()
    {
        $transaction = new KnetTransaction();
        $transaction->result = 'CAPTURED';

        $payment = new Payment($transaction);

        $this->assertTrue($payment->isSucceeded());
    }

    /** @test */
    public function it_can_return_not_captured()
    {
        $transaction = new KnetTransaction();
        $transaction->result = 'NOT CAPTURED';

        $payment = new Payment($transaction);

        $this->assertTrue($payment->isFailure());
    }

    /** @test */
    public function it_can_return_raw_amount()
    {
        $transaction = new KnetTransaction();
        $transaction->amt = 10;

        $payment = new Payment($transaction);

        $this->assertEquals(10, $payment->rawAmount());
    }

    /** @test */
    public function it_can_return_formatted_amount()
    {
        $transaction = new KnetTransaction();
        $transaction->amt = 10;

        $payment = new Payment($transaction);

        $this->assertEquals('10 KD', $payment->amount());
    }
}

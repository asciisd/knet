<?php


namespace Asciisd\Knet\Tests\Unit;


use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Tests\TestCase;

class PaymentTest extends TestCase
{
    /** @test */
    public function it_can_return_captured()
    {
        $transaction = new KnetTransaction();
        $transaction->result = 'CAPTURED';

        $this->assertTrue($transaction->isCaptured());
    }

    /** @test */
    public function it_can_return_not_captured()
    {
        $transaction = new KnetTransaction();
        $transaction->result = 'NOT CAPTURED';

        $this->assertTrue(!$transaction->isCaptured());
    }
}

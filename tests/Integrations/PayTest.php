<?php


namespace Asciisd\Knet\Tests\Integrations;


use Asciisd\Knet\Payment;

class PayTest extends IntegrationTestCase
{

    /** @test */
    public function customer_can_be_charged() {
        $user = $this->createCustomer('customer_can_be_charged');

        $response = $user->pay(1000);
        $this->assertInstanceOf(Payment::class, $response);
        $this->assertEquals(1000, $response->rawAmount());
        $this->assertEquals($user->id, $response->customer()->id);
    }
}
<?php

namespace Asciisd\Knet\Tests\Integration;

use Asciisd\Knet\Exceptions\PaymentActionRequired;
use Asciisd\Knet\KPayManager;
use Asciisd\Knet\Payment;

class PayTest extends IntegrationTestCase
{

    /** @test */
    public function customer_can_be_charged()
    {
        $user = $this->createCustomer('customer_can_be_charged');

        try {
            $response = $user->pay(100);
        } catch (PaymentActionRequired $e) {
            $response = $e->payment;
        }

        $this->assertInstanceOf(Payment::class, $response);
        $this->assertEquals(100, $response->rawAmount());
        $this->assertEquals($user->id, $response->owner()->id);
    }

    /** @test */
    public function customer_can_override_trackid()
    {
        $user = $this->createCustomer('customer_can_override_trackid');
        $trackid = 'teyYtsvvxbYUyw78767678';

        $this->expectException(PaymentActionRequired::class);

        $response = $user->pay(100, [
            'trackid' => $trackid,
        ]);

        $this->assertInstanceOf(Payment::class, $response);
        $this->assertEquals($trackid, $response->trackid);
        $this->assertEquals($user->id, $response->owner()->id);
    }

    /** @test */
    public function client_can_set_user_defined_properties()
    {
        $user = $this->createCustomer('customer_can_override_udf1');

        $this->expectException(PaymentActionRequired::class);

        $response = $user->pay(100, [
            'udf1' => $user->name,
            'udf2' => $user->email,
            'udf3' => '+12345678910',
            'udf4' => '0001',
            'udf5' => 'i have some notes',
        ]);

        $this->assertInstanceOf(Payment::class, $response);
        $this->assertEquals($user->id, $response->owner()->id);

        $this->assertEquals($user->name, $response->udf1);
        $this->assertEquals($user->email, $response->udf2);
        $this->assertEquals('+12345678910', $response->udf3);
        $this->assertEquals('0001', $response->udf4);
        $this->assertEquals('i have some notes', $response->udf5);
    }

    /** @test */
    public function it_can_created_from_facade_direct_without_user()
    {
        $user = $this->createCustomer('customer_can_use_facade');
        auth()->login($user);

        $response = KPayManager::make(100);

        $this->assertInstanceOf(KPayManager::class, $response);
        $this->assertEquals(auth()->id(), $response->toArray()['user_id']);
    }
}

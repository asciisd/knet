<?php

namespace Asciisd\Knet\Tests\Integration;

use Asciisd\Knet\Exceptions\PaymentActionRequired;
use Asciisd\Knet\KPayManager;
use Asciisd\Knet\KnetTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DepositTest extends IntegrationTestCase
{
    /** @test */
    public function it_can_create_a_deposit_transaction()
    {
        $user = $this->createCustomer('deposit_test_user');

        try {
            $response = $user->pay(100);
        } catch (PaymentActionRequired $e) {
            $response = $e->transaction;
        }

        $this->assertInstanceOf(KnetTransaction::class, $response);
        $this->assertEquals(100, $response->rawAmount());
        $this->assertEquals($user->getKey(), $response->owner()->getKey());
        $this->assertEquals('INITIATED', $response->result);
    }

    /** @test */
    public function it_can_create_a_deposit_with_custom_track_id()
    {
        $user = $this->createCustomer('deposit_custom_trackid');
        $trackid = 'test-' . Str::random(10);

        try {
            $response = $user->pay(100, [
                'trackid' => $trackid,
            ]);
        } catch (PaymentActionRequired $e) {
            $response = $e->transaction;
        }

        $this->assertInstanceOf(KnetTransaction::class, $response);
        $this->assertEquals($trackid, $response->trackid);
        $this->assertEquals($user->getKey(), $response->owner()->getKey());
    }

    /** @test */
    public function it_can_create_a_deposit_with_user_defined_fields()
    {
        $user = $this->createCustomer('deposit_with_udf');
        $orderNumber = 'ORD-' . rand(1000, 9999);

        try {
            $response = $user->pay(100, [
                'udf1' => $orderNumber,
                'udf2' => $user->email,
                'udf3' => 'Test Payment',
            ]);
        } catch (PaymentActionRequired $e) {
            $response = $e->transaction;
        }

        $this->assertInstanceOf(KnetTransaction::class, $response);
        $this->assertEquals($orderNumber, $response->udf1);
        $this->assertEquals($user->email, $response->udf2);
        $this->assertEquals('Test Payment', $response->udf3);
    }

    /** @test */
    public function it_can_create_a_deposit_with_direct_facade()
    {
        $user = $this->createCustomer('deposit_with_facade');
        Auth::login($user);

        $amount = 250;
        $response = KPayManager::make($amount);

        $this->assertInstanceOf(KPayManager::class, $response);
        $this->assertEquals($amount, $response->toArray()['amt']);
        $this->assertEquals($user->getKey(), $response->toArray()['user_id']);
        $this->assertNotNull($response->toArray()['url']);
    }

    /** @test */
    public function it_generates_valid_payment_url()
    {
        $user = $this->createCustomer('deposit_url_test');

        try {
            $response = $user->pay(100);
        } catch (PaymentActionRequired $e) {
            $response = $e->transaction;
        }

        $this->assertNotNull($response->url);
        $this->assertStringContainsString('param=paymentInit', $response->url);
        $this->assertStringContainsString('trandata=', $response->url);
        $this->assertStringContainsString('tranportalId=', $response->url);
    }
} 
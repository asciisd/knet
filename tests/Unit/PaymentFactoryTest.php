<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\DataTransferObjects\PaymentRequest;
use Asciisd\Knet\Enums\PaymentStatus;
use Asciisd\Knet\Factories\PaymentFactory;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Foundation\Auth\User;

class PaymentFactoryTest extends TestCase
{
    private function makeUser(): User
    {
        return new class extends User {
            protected $fillable = ['id', 'name', 'email'];
        };
    }

    public function test_create_request_returns_payment_request()
    {
        $user = $this->makeUser();
        $request = PaymentFactory::createRequest($user, 25.500);

        $this->assertInstanceOf(PaymentRequest::class, $request);
        $this->assertSame($user, $request->user);
        $this->assertEquals(25.500, $request->amount);
        $this->assertNotNull($request->trackId);
    }

    public function test_create_request_with_custom_options()
    {
        $request = PaymentFactory::createRequest($this->makeUser(), 10.000, [
            'trackid' => 'custom-track',
            'udf1' => 'order_1',
            'udf2' => 'meta_2',
            'udf5' => 'extra',
        ]);

        $this->assertEquals('custom-track', $request->trackId);
        $this->assertEquals('order_1', $request->udf1);
        $this->assertEquals('meta_2', $request->udf2);
        $this->assertNull($request->udf3);
        $this->assertNull($request->udf4);
        $this->assertEquals('extra', $request->udf5);
    }

    public function test_create_request_generates_uuid_track_id_when_not_provided()
    {
        $request1 = PaymentFactory::createRequest($this->makeUser(), 5.000);
        $request2 = PaymentFactory::createRequest($this->makeUser(), 5.000);

        $this->assertNotNull($request1->trackId);
        $this->assertNotNull($request2->trackId);
        $this->assertNotEquals($request1->trackId, $request2->trackId);
    }

    public function test_mock_successful_response_structure()
    {
        $response = PaymentFactory::mockSuccessfulResponse('track-abc');

        $this->assertIsArray($response);
        $this->assertEquals('track-abc', $response['trackid']);
        $this->assertEquals(PaymentStatus::CAPTURED->value, $response['result']);
        $this->assertEquals('100.000', $response['amt']);
        $this->assertArrayHasKey('paymentid', $response);
        $this->assertArrayHasKey('auth', $response);
        $this->assertArrayHasKey('ref', $response);
        $this->assertArrayHasKey('tranid', $response);
        $this->assertArrayHasKey('postdate', $response);
    }

    public function test_mock_successful_response_uses_provided_track_id()
    {
        $response1 = PaymentFactory::mockSuccessfulResponse('track-1');
        $response2 = PaymentFactory::mockSuccessfulResponse('track-2');

        $this->assertEquals('track-1', $response1['trackid']);
        $this->assertEquals('track-2', $response2['trackid']);
    }
}

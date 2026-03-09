<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\DataTransferObjects\PaymentRequest;
use Asciisd\Knet\Exceptions\KnetException;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Foundation\Auth\User;

class PaymentRequestTest extends TestCase
{
    private function makeUser(): User
    {
        return new class extends User {
            protected $fillable = ['id', 'name', 'email'];
        };
    }

    public function test_valid_payment_request_creation()
    {
        $user = $this->makeUser();
        $request = new PaymentRequest(
            user: $user,
            amount: 25.500,
            trackId: 'track-123',
            udf1: 'order_1',
        );

        $this->assertSame($user, $request->user);
        $this->assertEquals(25.500, $request->amount);
        $this->assertEquals('track-123', $request->trackId);
        $this->assertEquals('order_1', $request->udf1);
    }

    public function test_zero_amount_throws_exception()
    {
        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('Payment amount must be greater than zero');

        new PaymentRequest(user: $this->makeUser(), amount: 0);
    }

    public function test_negative_amount_throws_exception()
    {
        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('Payment amount must be greater than zero');

        new PaymentRequest(user: $this->makeUser(), amount: -10.000);
    }

    public function test_to_array_returns_non_null_fields()
    {
        $request = new PaymentRequest(
            user: $this->makeUser(),
            amount: 10.000,
            trackId: 'track-456',
            udf1: 'value1',
            udf3: 'value3',
        );

        $array = $request->toArray();

        $this->assertEquals('track-456', $array['trackid']);
        $this->assertEquals('value1', $array['udf1']);
        $this->assertEquals('value3', $array['udf3']);
        $this->assertArrayNotHasKey('udf2', $array);
        $this->assertArrayNotHasKey('udf4', $array);
        $this->assertArrayNotHasKey('udf5', $array);
    }

    public function test_to_array_with_no_optional_fields()
    {
        $request = new PaymentRequest(
            user: $this->makeUser(),
            amount: 5.000,
        );

        $array = $request->toArray();

        $this->assertEmpty($array);
    }

    public function test_to_array_with_all_udf_fields()
    {
        $request = new PaymentRequest(
            user: $this->makeUser(),
            amount: 1.000,
            trackId: 'tid',
            udf1: 'a',
            udf2: 'b',
            udf3: 'c',
            udf4: 'd',
            udf5: 'e',
        );

        $array = $request->toArray();

        $this->assertCount(6, $array);
        $this->assertEquals('tid', $array['trackid']);
        $this->assertEquals('a', $array['udf1']);
        $this->assertEquals('e', $array['udf5']);
    }
}

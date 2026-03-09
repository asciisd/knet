<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\DataTransferObjects\KnetResponse;
use Asciisd\Knet\Tests\TestCase;

class KnetResponseTest extends TestCase
{
    public function test_from_array_with_full_captured_payload()
    {
        $data = [
            'paymentid' => 'PAY123',
            'trackid' => 'TRACK456',
            'result' => 'CAPTURED',
            'auth' => 'AUTH789',
            'ref' => 'REF012',
            'tranid' => 'TRAN345',
            'postdate' => '20250301',
            'error_text' => null,
            'amt' => '25.500',
            'avr' => 'Y',
            'udf1' => 'order_1',
            'udf2' => 'meta_2',
            'udf3' => null,
            'udf4' => null,
            'udf5' => null,
        ];

        $response = KnetResponse::fromArray($data);

        $this->assertEquals('PAY123', $response->paymentId);
        $this->assertEquals('TRACK456', $response->trackId);
        $this->assertEquals('CAPTURED', $response->result);
        $this->assertEquals('AUTH789', $response->auth);
        $this->assertEquals('REF012', $response->reference);
        $this->assertEquals('TRAN345', $response->transactionId);
        $this->assertEquals('20250301', $response->postDate);
        $this->assertNull($response->errorText);
        $this->assertEquals('25.500', $response->amount);
        $this->assertTrue($response->paid);
        $this->assertEquals('Y', $response->avr);
        $this->assertEquals('order_1', $response->udf1);
        $this->assertEquals('meta_2', $response->udf2);
    }

    public function test_from_array_marks_paid_true_only_for_captured()
    {
        $captured = KnetResponse::fromArray(['result' => 'CAPTURED']);
        $this->assertTrue($captured->paid);

        $failed = KnetResponse::fromArray(['result' => 'FAILED']);
        $this->assertFalse($failed->paid);

        $success = KnetResponse::fromArray(['result' => 'SUCCESS']);
        $this->assertFalse($success->paid);
    }

    public function test_from_array_with_minimal_data()
    {
        $response = KnetResponse::fromArray([]);

        $this->assertEquals('', $response->paymentId);
        $this->assertEquals('', $response->trackId);
        $this->assertEquals('', $response->result);
        $this->assertNull($response->auth);
        $this->assertNull($response->reference);
        $this->assertNull($response->transactionId);
        $this->assertNull($response->postDate);
        $this->assertNull($response->errorText);
        $this->assertEquals('0.000', $response->amount);
        $this->assertFalse($response->paid);
    }

    public function test_from_array_with_all_udf_fields()
    {
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data["udf{$i}"] = "value_{$i}";
        }

        $response = KnetResponse::fromArray($data);

        for ($i = 1; $i <= 10; $i++) {
            $prop = "udf{$i}";
            $this->assertEquals("value_{$i}", $response->$prop);
        }
    }

    public function test_from_array_with_error_payload()
    {
        $data = [
            'paymentid' => 'PAY_ERR',
            'trackid' => 'TRACK_ERR',
            'result' => 'FAILED',
            'error_text' => 'Card declined by issuer',
            'amt' => '10.000',
        ];

        $response = KnetResponse::fromArray($data);

        $this->assertFalse($response->paid);
        $this->assertEquals('FAILED', $response->result);
        $this->assertEquals('Card declined by issuer', $response->errorText);
    }
}

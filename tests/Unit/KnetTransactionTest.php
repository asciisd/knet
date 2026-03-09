<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Tests\TestCase;

class KnetTransactionTest extends TestCase
{
    public function test_is_captured_returns_true_for_captured_result()
    {
        $transaction = new KnetTransaction(['result' => 'CAPTURED']);

        $this->assertTrue($transaction->isCaptured());
    }

    public function test_is_captured_returns_false_for_non_captured_result()
    {
        $transaction = new KnetTransaction(['result' => 'FAILED']);
        $this->assertFalse($transaction->isCaptured());

        $transaction = new KnetTransaction(['result' => 'SUCCESS']);
        $this->assertFalse($transaction->isCaptured());

        $transaction = new KnetTransaction(['result' => null]);
        $this->assertFalse($transaction->isCaptured());
    }

    public function test_has_status_returns_true_when_result_is_set()
    {
        $transaction = new KnetTransaction(['result' => 'CAPTURED']);
        $this->assertTrue($transaction->hasStatus());

        $transaction = new KnetTransaction(['result' => 'FAILED']);
        $this->assertTrue($transaction->hasStatus());
    }

    public function test_has_status_returns_false_when_result_is_empty()
    {
        $transaction = new KnetTransaction(['result' => null]);
        $this->assertFalse($transaction->hasStatus());

        $transaction = new KnetTransaction(['result' => '']);
        $this->assertFalse($transaction->hasStatus());
    }

    public function test_raw_amount_returns_amt_as_float()
    {
        $transaction = new KnetTransaction(['amt' => '25.500']);

        $this->assertEquals(25.5, $transaction->rawAmount());
        $this->assertIsFloat($transaction->rawAmount());
    }

    public function test_is_refundable_when_captured_and_not_refunded()
    {
        $transaction = new KnetTransaction([
            'result' => 'CAPTURED',
            'refunded' => false,
        ]);

        $this->assertTrue($transaction->isRefundable());
    }

    public function test_is_not_refundable_when_already_refunded()
    {
        $transaction = new KnetTransaction([
            'result' => 'CAPTURED',
            'refunded' => true,
        ]);

        $this->assertFalse($transaction->isRefundable());
    }

    public function test_is_not_refundable_when_not_captured()
    {
        $transaction = new KnetTransaction([
            'result' => 'FAILED',
            'refunded' => false,
        ]);

        $this->assertFalse($transaction->isRefundable());
    }

    public function test_is_not_refundable_when_pending()
    {
        $transaction = new KnetTransaction([
            'result' => 'PENDING',
            'refunded' => false,
        ]);

        $this->assertFalse($transaction->isRefundable());
    }

    public function test_use_customer_model_changes_model_class()
    {
        $original = KnetTransaction::$customerModel;

        KnetTransaction::useCustomerModel('App\\Models\\Customer');
        $this->assertEquals('App\\Models\\Customer', KnetTransaction::$customerModel);

        KnetTransaction::useCustomerModel($original);
    }

    public function test_find_by_track_id_with_database()
    {
        $transaction = KnetTransaction::create([
            'trackid' => 'test-track-123',
            'user_id' => 1,
        ]);

        $found = KnetTransaction::findByTrackId('test-track-123');
        $this->assertEquals($transaction->id, $found->id);
        $this->assertEquals('test-track-123', $found->trackid);
    }

    public function test_find_by_track_id_throws_when_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        KnetTransaction::findByTrackId('nonexistent-track-id');
    }

    public function test_fillable_attributes()
    {
        $transaction = new KnetTransaction([
            'user_id' => 1,
            'trackid' => 'test',
            'result' => 'CAPTURED',
            'paid' => true,
            'amt' => '10.000',
            'udf1' => 'val1',
            'udf5' => 'val5',
            'refunded' => false,
            'refund_amount' => 0,
        ]);

        $this->assertEquals(1, $transaction->user_id);
        $this->assertEquals('test', $transaction->trackid);
        $this->assertEquals('CAPTURED', $transaction->result);
        $this->assertTrue($transaction->paid);
        $this->assertEquals('val1', $transaction->udf1);
        $this->assertEquals('val5', $transaction->udf5);
    }

    public function test_casts_refunded_to_boolean()
    {
        $transaction = new KnetTransaction(['refunded' => 1]);
        $this->assertTrue($transaction->refunded);

        $transaction = new KnetTransaction(['refunded' => 0]);
        $this->assertFalse($transaction->refunded);
    }

    public function test_casts_refund_amount_to_float()
    {
        $transaction = new KnetTransaction(['refund_amount' => '5.000']);
        $this->assertIsFloat($transaction->refund_amount);
        $this->assertEquals(5.0, $transaction->refund_amount);
    }
}

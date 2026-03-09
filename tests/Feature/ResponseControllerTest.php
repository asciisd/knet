<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Events\KnetPaymentFailed;
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ResponseControllerTest extends TestCase
{

    public function test_successful_captured_payment_updates_transaction()
    {
        Event::fake();

        $transaction = KnetTransaction::create([
            'trackid' => 'track-success-1',
            'user_id' => 1,
            'amt' => '25.500',
            'result' => 'INITIATED',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_SUCCESS_1',
            'trackid' => 'track-success-1',
            'result' => 'CAPTURED',
            'auth' => 'AUTH123',
            'ref' => 'REF456',
            'tranid' => 'TRAN789',
            'amt' => '25.500',
            'postdate' => '20250301',
        ]);

        $response = $this->post(route('knet.response.store'), [
            'trandata' => $trandata,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('REDIRECT=', $response->getContent());
        $this->assertStringContainsString(route('knet.handle'), $response->getContent());

        $transaction->refresh();
        $this->assertEquals('CAPTURED', $transaction->result);
        $this->assertTrue((bool) $transaction->paid);
        $this->assertEquals('AUTH123', $transaction->auth);
        $this->assertEquals('REF456', $transaction->ref);
        $this->assertEquals('TRAN789', $transaction->tranid);

        Event::assertDispatched(KnetResponseReceived::class);
        Event::assertDispatched(KnetResponseHandled::class);
        Event::assertDispatched(KnetPaymentSucceeded::class);
    }

    public function test_failed_payment_updates_transaction_and_fires_failed_event()
    {
        Event::fake();

        KnetTransaction::create([
            'trackid' => 'track-fail-1',
            'user_id' => 1,
            'amt' => '10.000',
            'result' => 'INITIATED',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_FAIL_1',
            'trackid' => 'track-fail-1',
            'result' => 'NOT CAPTURED',
            'amt' => '10.000',
        ]);

        $response = $this->post(route('knet.response.store'), [
            'trandata' => $trandata,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('REDIRECT=', $response->getContent());

        $transaction = KnetTransaction::where('trackid', 'track-fail-1')->first();
        $this->assertEquals('NOT CAPTURED', $transaction->result);
        $this->assertFalse((bool) $transaction->paid);

        Event::assertDispatched(KnetPaymentFailed::class);
        Event::assertNotDispatched(KnetPaymentSucceeded::class);
    }

    public function test_missing_trandata_is_rejected_by_middleware()
    {
        $response = $this->post(route('knet.response.store'), []);

        $response->assertForbidden();
    }

    public function test_invalid_hex_trandata_is_rejected_by_middleware()
    {
        $response = $this->post(route('knet.response.store'), [
            'trandata' => 'not_valid_hex_data!@#$%',
        ]);

        $response->assertForbidden();
    }

    public function test_nonexistent_trackid_is_rejected()
    {
        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_GHOST',
            'trackid' => 'nonexistent-track-id',
            'result' => 'CAPTURED',
            'amt' => '5.000',
        ]);

        $response = $this->post(route('knet.response.store'), [
            'trandata' => $trandata,
        ]);

        $response->assertStatus(404);
    }

    public function test_events_contain_correct_payload()
    {
        Event::fake();

        KnetTransaction::create([
            'trackid' => 'track-event-1',
            'user_id' => 1,
            'amt' => '50.000',
            'result' => 'INITIATED',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_EVENT_1',
            'trackid' => 'track-event-1',
            'result' => 'CAPTURED',
            'auth' => 'AUTHEVT',
            'amt' => '50.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        Event::assertDispatched(KnetResponseReceived::class, function ($event) {
            return $event->payload['trackid'] === 'track-event-1'
                && $event->payload['result'] === 'CAPTURED';
        });

        Event::assertDispatched(KnetPaymentSucceeded::class, function ($event) {
            return $event->transaction->trackid === 'track-event-1';
        });
    }

    public function test_response_with_udf_fields()
    {
        Event::fake();

        KnetTransaction::create([
            'trackid' => 'track-udf-1',
            'user_id' => 1,
            'amt' => '15.000',
            'result' => 'INITIATED',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_UDF_1',
            'trackid' => 'track-udf-1',
            'result' => 'CAPTURED',
            'amt' => '15.000',
            'udf1' => 'order_100',
            'udf2' => 'invoice_200',
            'udf3' => 'custom_data',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction = KnetTransaction::where('trackid', 'track-udf-1')->first();
        $this->assertEquals('order_100', $transaction->udf1);
        $this->assertEquals('invoice_200', $transaction->udf2);
        $this->assertEquals('custom_data', $transaction->udf3);
    }
}

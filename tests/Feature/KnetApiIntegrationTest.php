<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Events\KnetPaymentFailed;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetInquiryService;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Services\KnetRefundService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class KnetApiIntegrationTest extends TestCase
{
    private KnetPaymentService $paymentService;

    private KnetInquiryService $inquiryService;

    private KnetRefundService $refundService;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->setUpUsersTable();

        $this->paymentService = $this->app->make(KnetPaymentService::class);
        $this->inquiryService = $this->app->make(KnetInquiryService::class);
        $this->refundService = $this->app->make(KnetRefundService::class);
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function createUser(): User
    {
        return $this->createDbUser();
    }

    /**
     * Full lifecycle: create -> callback -> inquiry -> refund
     */
    public function test_complete_payment_lifecycle_with_mocked_api()
    {
        Event::fake();
        $user = $this->createUser();

        // 1. Create payment
        $transaction = $this->paymentService->createPayment($user, 50.000, [
            'udf1' => 'order_lifecycle_001',
            'trackid' => 'LIFECYCLE-001',
        ]);

        $this->assertEquals('INITIATED', $transaction->result);
        $this->assertStringContainsString('kpaytest.com.kw', $transaction->url);

        // 2. Simulate KNET callback (successful payment)
        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_PAY_LC001',
            'trackid' => 'LIFECYCLE-001',
            'result' => 'CAPTURED',
            'auth' => 'LC_AUTH',
            'ref' => 'LC_REF_123',
            'tranid' => 'LC_TRAN_456',
            'amt' => '50.000',
            'postdate' => '20260309',
            'udf1' => 'order_lifecycle_001',
        ]);

        $response = $this->post(route('knet.response.store'), ['trandata' => $trandata]);
        $response->assertOk();
        $this->assertStringContainsString('REDIRECT=', $response->getContent());

        $transaction->refresh();
        $this->assertEquals('CAPTURED', $transaction->result);
        $this->assertTrue((bool) $transaction->paid);
        $this->assertEquals('LC_AUTH', $transaction->auth);
        $this->assertEquals('LC_REF_123', $transaction->ref);
        $this->assertTrue($transaction->isRefundable());

        // 3. Inquiry to verify status via mocked API
        KnetApiMock::fakeInquirySuccess([
            'trackid' => 'LIFECYCLE-001',
            'amt' => '50.000',
            'auth' => 'LC_AUTH',
            'ref' => 'LC_REF_123',
            'tranid' => 'LC_TRAN_456',
            'payid' => 'KNET_PAY_LC001',
            'udf1' => 'order_lifecycle_001',
        ]);

        $inquiryResult = $this->inquiryService->inquirePayment(50.000, 'LIFECYCLE-001');
        $this->assertEquals('CAPTURED', $inquiryResult['result']);
        $this->assertEquals('LC_AUTH', $inquiryResult['auth']);

        // 4. Full refund via mocked API
        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'LIFECYCLE-001',
            'amt' => '50.000',
            'auth' => 'REFUND_AUTH_LC',
            'ref' => 'REFUND_REF_LC',
            'tranid' => 'REFUND_TRAN_LC',
            'payid' => 'REFUND_PAY_LC',
        ]);

        $refundResult = $this->refundService->refundPayment($transaction);
        $this->assertEquals('CAPTURED', $refundResult['result']);

        $transaction->refresh();
        $this->assertTrue($transaction->refunded);
        $this->assertNotNull($transaction->refunded_at);
        $this->assertFalse($transaction->isRefundable());
    }

    /**
     * Lifecycle: create -> callback (failed) -> inquiry confirms failure
     */
    public function test_failed_payment_lifecycle()
    {
        Event::fake();
        $user = $this->createUser();

        $transaction = $this->paymentService->createPayment($user, 15.000, [
            'trackid' => 'FAIL-LC-001',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_FAIL_LC001',
            'trackid' => 'FAIL-LC-001',
            'result' => 'NOT CAPTURED',
            'amt' => '15.000',
            'error_text' => 'Card declined',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction->refresh();
        $this->assertEquals('NOT CAPTURED', $transaction->result);
        $this->assertFalse((bool) $transaction->paid);
        $this->assertFalse($transaction->isRefundable());

        Event::assertDispatched(KnetPaymentFailed::class);

        KnetApiMock::fakeInquiryNotCaptured([
            'trackid' => 'FAIL-LC-001',
            'amt' => '15.000',
        ]);

        $inquiryResult = $this->inquiryService->inquirePayment(15.000, 'FAIL-LC-001');
        $this->assertEquals('NOT CAPTURED', $inquiryResult['result']);
    }

    /**
     * Lifecycle: create -> callback -> partial refund
     */
    public function test_partial_refund_lifecycle()
    {
        Event::fake();
        $user = $this->createUser();

        $transaction = $this->paymentService->createPayment($user, 100.000, [
            'trackid' => 'PARTIAL-LC-001',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_PART_001',
            'trackid' => 'PARTIAL-LC-001',
            'result' => 'CAPTURED',
            'auth' => 'PART_AUTH',
            'ref' => 'PART_REF',
            'tranid' => 'PART_TRAN',
            'amt' => '100.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction->refresh();
        $this->assertTrue($transaction->isCaptured());

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'PARTIAL-LC-001',
            'amt' => '30.000',
            'auth' => 'PARTIAL_REF_AUTH',
            'ref' => 'PARTIAL_REF_REF',
            'tranid' => 'PARTIAL_REF_TRAN',
            'payid' => 'PARTIAL_REF_PAY',
        ]);

        $refundResult = $this->refundService->refundPayment($transaction, 30.000);
        $this->assertEquals('CAPTURED', $refundResult['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<amt>30.000</amt>')
                && str_contains($request->body(), '<action>2</action>');
        });
    }

    public function test_callback_events_fire_in_correct_order()
    {
        $firedEvents = [];
        Event::listen('*', function ($eventName) use (&$firedEvents) {
            if (str_starts_with($eventName, 'Asciisd\\Knet\\Events\\')) {
                $firedEvents[] = class_basename($eventName);
            }
        });

        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000, [
            'trackid' => 'EVENTS-ORDER-001',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_EVT_001',
            'trackid' => 'EVENTS-ORDER-001',
            'result' => 'CAPTURED',
            'auth' => 'EVT_AUTH',
            'amt' => '10.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $this->assertContains('KnetResponseReceived', $firedEvents);
        $this->assertContains('KnetResponseHandled', $firedEvents);
        $this->assertContains('KnetPaymentSucceeded', $firedEvents);

        $receivedIdx = array_search('KnetResponseReceived', $firedEvents);
        $handledIdx = array_search('KnetResponseHandled', $firedEvents);
        $succeededIdx = array_search('KnetPaymentSucceeded', $firedEvents);

        $this->assertLessThan($handledIdx, $receivedIdx);
    }

    public function test_error_controller_handles_payment_failure_with_error_details()
    {
        $response = $this->post(route('knet.error'), [
            'paymentid' => 'ERR_PAY_001',
            'result' => 'FAILED',
            'error' => 'IPAY0100114',
            'error_text' => 'Insufficient funds',
        ]);

        $response->assertRedirect(config('knet.redirect_url'));
        $response->assertSessionHas('knet_error', function (array $data) {
            return $data['paymentid'] === 'ERR_PAY_001'
                && $data['result'] === 'FAILED'
                && $data['error'] === 'IPAY0100114'
                && $data['error_text'] === 'Insufficient funds';
        });
    }

    public function test_handle_controller_redirects_after_payment()
    {
        $response = $this->post(route('knet.handle'));
        $response->assertRedirect(config('knet.redirect_url'));
    }

    public function test_invalid_trandata_in_response_returns_error()
    {
        $response = $this->post(route('knet.response.store'), [
            'trandata' => 'definitely_not_valid_hex!@#',
        ]);

        $response->assertForbidden();
    }

    public function test_empty_post_to_response_returns_error()
    {
        $response = $this->post(route('knet.response.store'), []);
        $response->assertForbidden();
    }

    public function test_inquiry_after_callback_matches_data()
    {
        Event::fake();
        $user = $this->createUser();

        $transaction = $this->paymentService->createPayment($user, 35.750, [
            'trackid' => 'MATCH-001',
            'udf1' => 'invoice_555',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_MATCH_001',
            'trackid' => 'MATCH-001',
            'result' => 'CAPTURED',
            'auth' => 'MATCH_AUTH',
            'ref' => 'MATCH_REF',
            'tranid' => 'MATCH_TRAN',
            'amt' => '35.750',
            'udf1' => 'invoice_555',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction->refresh();

        KnetApiMock::fakeInquirySuccess([
            'trackid' => 'MATCH-001',
            'amt' => '35.750',
            'auth' => 'MATCH_AUTH',
            'ref' => 'MATCH_REF',
            'tranid' => 'MATCH_TRAN',
            'payid' => 'KNET_MATCH_001',
            'udf1' => 'invoice_555',
        ]);

        $updated = $this->inquiryService->inquireAndUpdateTransaction($transaction);

        $this->assertEquals('CAPTURED', $updated->result);
        $this->assertEquals('MATCH_AUTH', $updated->auth);
        $this->assertEquals('MATCH_REF', $updated->ref);
        $this->assertEquals('invoice_555', $updated->udf1);
    }

    public function test_inquiry_sequence_shows_status_progression()
    {
        Event::fake();

        $transaction = KnetTransaction::create([
            'trackid' => 'SEQ-001',
            'user_id' => 1,
            'amt' => '20.000',
            'result' => 'INITIATED',
        ]);

        KnetApiMock::fakeSequence([
            [
                'result' => 'INITIATED',
                'auth' => '',
                'ref' => '',
                'avr' => '',
                'postdate' => '',
                'tranid' => '',
                'trackid' => 'SEQ-001',
                'payid' => '',
                'amt' => '20.000',
                'udf1' => '',
                'udf2' => '',
                'udf3' => '',
                'udf4' => '',
                'udf5' => '',
            ],
            [
                'result' => 'CAPTURED',
                'auth' => 'SEQ_AUTH',
                'ref' => 'SEQ_REF',
                'avr' => 'N',
                'postdate' => '03091530',
                'tranid' => 'SEQ_TRAN',
                'trackid' => 'SEQ-001',
                'payid' => 'SEQ_PAY',
                'amt' => '20.000',
                'udf1' => '',
                'udf2' => '',
                'udf3' => '',
                'udf4' => '',
                'udf5' => '',
            ],
        ]);

        $firstResult = $this->inquiryService->inquirePayment(20.000, 'SEQ-001');
        $this->assertEquals('INITIATED', $firstResult['result']);

        $secondResult = $this->inquiryService->inquirePayment(20.000, 'SEQ-001');
        $this->assertEquals('CAPTURED', $secondResult['result']);
        $this->assertEquals('SEQ_AUTH', $secondResult['auth']);
    }

    public function test_refund_api_failure_does_not_corrupt_original_transaction()
    {
        Event::fake();
        $user = $this->createUser();

        $transaction = $this->paymentService->createPayment($user, 40.000, [
            'trackid' => 'SAFE-001',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'KNET_SAFE_001',
            'trackid' => 'SAFE-001',
            'result' => 'CAPTURED',
            'auth' => 'SAFE_AUTH',
            'ref' => 'SAFE_REF',
            'tranid' => 'SAFE_TRAN',
            'amt' => '40.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);
        $transaction->refresh();

        $originalAuth = $transaction->auth;
        $originalRef = $transaction->ref;

        KnetApiMock::fakeServerError();

        try {
            $this->refundService->refundPayment($transaction);
        } catch (\RuntimeException $e) {
            // Expected
        }

        $transaction->refresh();
        $this->assertEquals('CAPTURED', $transaction->result);
        $this->assertEquals($originalAuth, $transaction->auth);
        $this->assertEquals($originalRef, $transaction->ref);
        $this->assertFalse($transaction->refunded);
        $this->assertTrue($transaction->isRefundable());
    }

    public function test_response_callback_with_raw_post_content()
    {
        Event::fake();

        KnetTransaction::create([
            'trackid' => 'RAW-POST-001',
            'user_id' => 1,
            'amt' => '10.000',
            'result' => 'INITIATED',
        ]);

        $trandata = $this->encryptPayload([
            'paymentid' => 'RAW_PAY_001',
            'trackid' => 'RAW-POST-001',
            'result' => 'CAPTURED',
            'auth' => 'RAW_AUTH',
            'amt' => '10.000',
        ]);

        $response = $this->call('POST', route('knet.response.store'), [
            'trandata' => $trandata,
        ]);

        $response->assertOk();

        $transaction = KnetTransaction::where('trackid', 'RAW-POST-001')->first();
        $this->assertEquals('CAPTURED', $transaction->result);
    }

    public function test_get_error_endpoint_with_all_parameters()
    {
        $response = $this->get(route('knet.error.get', [
            'paymentid' => 'GET_ERR_001',
            'result' => 'FAILED',
            'error' => 'IPAY0100055',
            'error_text' => 'Transaction timed out',
        ]));

        $response->assertRedirect(config('knet.redirect_url'));
        $response->assertSessionHas('knet_error', function (array $data) {
            return $data['error_text'] === 'Transaction timed out'
                && $data['error'] === 'IPAY0100055';
        });
    }
}

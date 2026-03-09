<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetInquiryService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class KnetInquiryApiTest extends TestCase
{
    private KnetInquiryService $inquiryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inquiryService = $this->app->make(KnetInquiryService::class);
    }

    public function test_inquiry_returns_captured_status_for_successful_payment()
    {
        $trackId = 'INQ-CAPTURED-001';

        KnetApiMock::fakeInquirySuccess([
            'trackid' => $trackId,
            'amt' => '25.500',
            'auth' => 'A99887',
            'ref' => '327200054321',
            'tranid' => '5544332211001',
        ]);

        $result = $this->inquiryService->inquirePayment(25.500, $trackId);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('A99887', $result['auth']);
        $this->assertEquals('327200054321', $result['ref']);
        $this->assertEquals('5544332211001', $result['tranid']);
        $this->assertEquals($trackId, $result['trackid']);
        $this->assertEquals('25.500', $result['amt']);
    }

    public function test_inquiry_returns_not_captured_for_failed_payment()
    {
        $trackId = 'INQ-NOTCAP-001';

        KnetApiMock::fakeInquiryNotCaptured([
            'trackid' => $trackId,
            'amt' => '10.000',
        ]);

        $result = $this->inquiryService->inquirePayment(10.000, $trackId);

        $this->assertEquals('NOT CAPTURED', $result['result']);
        $this->assertNull($result['auth']);
        $this->assertNull($result['ref']);
    }

    public function test_inquiry_returns_pending_for_initiated_transaction()
    {
        $trackId = 'INQ-PENDING-001';

        KnetApiMock::fakeInquiryPending([
            'trackid' => $trackId,
            'amt' => '5.000',
        ]);

        $result = $this->inquiryService->inquirePayment(5.000, $trackId);

        $this->assertEquals('INITIATED', $result['result']);
        $this->assertNull($result['auth']);
        $this->assertNull($result['tranid']);
    }

    public function test_inquiry_error_transaction_not_found()
    {
        KnetApiMock::fakeInquiryError('IPAY0100263', 'Transaction not found.');

        $result = $this->inquiryService->inquirePayment(10.000, 'INQ-ERR-001');

        $this->assertEquals('ERROR', $result['result']);
        $this->assertEquals('IPAY0100263', $result['error_code']);
        $this->assertEquals('Transaction not found.', $result['error_message']);
    }

    public function test_inquiry_error_invalid_amount()
    {
        KnetApiMock::fakeInquiryError('IPAY0100062', 'Invalid Transaction Amount.');

        $result = $this->inquiryService->inquirePayment(-1.000, 'INQ-ERR-002');

        $this->assertEquals('ERROR', $result['result']);
        $this->assertEquals('IPAY0100062', $result['error_code']);
    }

    public function test_inquiry_error_includes_error_code_tag_and_service_tag()
    {
        KnetApiMock::fakeInquiryError('IPAY0100057', 'Action not supported');

        $result = $this->inquiryService->inquirePayment(10.000, 'INQ-ERR-003');

        $this->assertEquals('ERROR', $result['result']);
        $this->assertEquals('IPAY0100057', $result['error_code']);
        $this->assertArrayHasKey('error_code_tag', $result);
        $this->assertEquals('IPAY0100057', $result['error_code_tag']);
        $this->assertArrayHasKey('error_service_tag', $result);
    }

    public function test_inquiry_sends_correct_xml_to_knet_api()
    {
        Http::fake([
            KnetApiMock::inquiryUrl().'*' => Http::response(
                '<result>CAPTURED</result><auth>X</auth><ref>X</ref>'
                .'<avr>X</avr><postdate>X</postdate><tranid>X</tranid>'
                .'<trackid>TRACK-XML-001</trackid><payid>X</payid><amt>15.000</amt>'
                .'<udf1></udf1><udf2></udf2><udf3></udf3><udf4></udf4><udf5></udf5>'
            ),
        ]);

        $this->inquiryService->inquirePayment(15.000, 'TRACK-XML-001');

        Http::assertSent(function ($request) {
            $body = $request->body();
            $expectedUrl = KnetApiMock::inquiryUrl().'?param=tranInit';

            return $request->url() === $expectedUrl
                && str_contains($body, '<id>'.KnetApiMock::transportId().'</id>')
                && str_contains($body, '<password>'.KnetApiMock::transportPassword().'</password>')
                && str_contains($body, '<action>8</action>')
                && str_contains($body, '<amt>15.000</amt>')
                && str_contains($body, '<trackid>TRACK-XML-001</trackid>')
                && str_contains($body, '<udf5>TrackID</udf5>');
        });
    }

    public function test_inquiry_updates_transaction_with_captured_result()
    {
        Event::fake([KnetTransactionUpdated::class]);

        $transaction = KnetTransaction::create([
            'trackid' => 'INQ-UPDATE-001',
            'user_id' => 1,
            'amt' => '30.000',
            'result' => 'INITIATED',
        ]);

        KnetApiMock::fakeInquirySuccess([
            'trackid' => 'INQ-UPDATE-001',
            'amt' => '30.000',
            'result' => 'SUCCESS',
            'auth' => 'AUTHUPD',
            'ref' => 'REFUPD123',
            'tranid' => 'TRNUPD456',
            'postdate' => '20260301',
            'payid' => 'PAYUPD789',
        ]);

        $updated = $this->inquiryService->inquireAndUpdateTransaction($transaction);

        $this->assertEquals('SUCCESS', $updated->result);
        $this->assertEquals('AUTHUPD', $updated->auth);
        $this->assertEquals('REFUPD123', $updated->ref);
        $this->assertEquals('TRNUPD456', $updated->tranid);
        $this->assertTrue((bool) $updated->paid);
    }

    public function test_inquiry_updates_transaction_with_failed_result()
    {
        Event::fake();

        $transaction = KnetTransaction::create([
            'trackid' => 'INQ-FAIL-UPD-001',
            'user_id' => 1,
            'amt' => '20.000',
            'result' => 'INITIATED',
        ]);

        KnetApiMock::fakeInquiryNotCaptured([
            'trackid' => 'INQ-FAIL-UPD-001',
            'amt' => '20.000',
        ]);

        $updated = $this->inquiryService->inquireAndUpdateTransaction($transaction);

        $this->assertEquals('NOT CAPTURED', $updated->result);
        $this->assertFalse((bool) $updated->paid);
    }

    public function test_inquiry_dispatches_updated_event_on_change()
    {
        Event::fake([KnetTransactionUpdated::class]);

        $transaction = KnetTransaction::create([
            'trackid' => 'INQ-EVENT-001',
            'user_id' => 1,
            'amt' => '10.000',
            'result' => 'INITIATED',
        ]);

        KnetApiMock::fakeInquirySuccess([
            'trackid' => 'INQ-EVENT-001',
            'amt' => '10.000',
        ]);

        $this->inquiryService->inquireAndUpdateTransaction($transaction);

        Event::assertDispatched(KnetTransactionUpdated::class, function ($event) {
            return $event->transaction->trackid === 'INQ-EVENT-001';
        });
    }

    public function test_inquiry_preserves_udf_fields_from_api_response()
    {
        Event::fake();

        $transaction = KnetTransaction::create([
            'trackid' => 'INQ-UDF-001',
            'user_id' => 1,
            'amt' => '50.000',
            'result' => 'INITIATED',
            'udf1' => 'order_555',
        ]);

        KnetApiMock::fakeInquirySuccess([
            'trackid' => 'INQ-UDF-001',
            'amt' => '50.000',
            'udf1' => 'order_555',
            'udf2' => 'invoice_777',
        ]);

        $updated = $this->inquiryService->inquireAndUpdateTransaction($transaction);

        $this->assertEquals('order_555', $updated->udf1);
        $this->assertEquals('invoice_777', $updated->udf2);
    }

    public function test_inquiry_throws_on_server_error()
    {
        KnetApiMock::fakeServerError();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get response from KNET');

        $this->inquiryService->inquirePayment(10.000, 'INQ-500-001');
    }

    public function test_inquiry_throws_on_empty_response()
    {
        KnetApiMock::fakeEmptyResponse();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response received from KNET');

        $this->inquiryService->inquirePayment(10.000, 'INQ-EMPTY-001');
    }

    public function test_inquiry_throws_on_connection_timeout()
    {
        KnetApiMock::fakeTimeout();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get response from KNET');

        $this->inquiryService->inquirePayment(10.000, 'INQ-TIMEOUT-001');
    }

    public function test_inquiry_handles_json_response_format()
    {
        KnetApiMock::fakeJsonResponse([
            'result' => 'CAPTURED',
            'auth' => 'JSONAUTH',
            'ref' => 'JSONREF',
            'tranid' => 'JSONTRAN',
            'trackid' => 'INQ-JSON-001',
            'payid' => 'JSONPAY',
            'amt' => '99.000',
        ]);

        $result = $this->inquiryService->inquirePayment(99.000, 'INQ-JSON-001');

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('JSONAUTH', $result['auth']);
        $this->assertEquals('JSONREF', $result['ref']);
    }

    public function test_inquiry_formats_amount_with_three_decimals()
    {
        Http::fake([
            KnetApiMock::inquiryUrl().'*' => Http::response(
                '<result>CAPTURED</result><auth>X</auth><ref>X</ref>'
                .'<avr>X</avr><postdate>X</postdate><tranid>X</tranid>'
                .'<trackid>AMT-FMT-001</trackid><payid>X</payid><amt>0.250</amt>'
                .'<udf1></udf1><udf2></udf2><udf3></udf3><udf4></udf4><udf5></udf5>'
            ),
        ]);

        $this->inquiryService->inquirePayment(0.25, 'AMT-FMT-001');

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<amt>0.250</amt>');
        });
    }
}

<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetRefundService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class KnetRefundApiTest extends TestCase
{
    private KnetRefundService $refundService;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->refundService = $this->app->make(KnetRefundService::class);
    }

    private function createCapturedTransaction(array $overrides = []): KnetTransaction
    {
        return KnetTransaction::create(array_merge([
            'trackid' => 'REF-TRACK-'.uniqid(),
            'user_id' => 1,
            'amt' => '25.500',
            'result' => 'CAPTURED',
            'paid' => true,
            'auth' => 'ORIGAUTH',
            'ref' => 'ORIGREF',
            'tranid' => 'ORIGTRAN',
            'paymentid' => 'ORIGPAY',
            'livemode' => false,
        ], $overrides));
    }

    public function test_full_refund_succeeds_and_updates_original_transaction()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-FULL-001',
            'amt' => '25.500',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-FULL-001',
            'amt' => '25.500',
            'auth' => 'REFAUTH01',
            'ref' => 'REFREF001',
            'tranid' => 'REFTRAN01',
            'payid' => 'REFPAY001',
        ]);

        $result = $this->refundService->refundPayment($transaction);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('REFAUTH01', $result['auth']);

        $transaction->refresh();
        $this->assertTrue($transaction->refunded);
        $this->assertNotNull($transaction->refunded_at);
    }

    public function test_partial_refund_sends_correct_amount()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-PARTIAL-001',
            'amt' => '50.000',
        ]);

        Http::fake([
            KnetApiMock::inquiryUrl().'*' => Http::response(
                '<result>CAPTURED</result><auth>PAUTH</auth><ref>PREF</ref>'
                .'<avr></avr><postdate>'.now()->format('mdHi').'</postdate>'
                .'<tranid>PTRAN</tranid><trackid>REF-PARTIAL-001</trackid>'
                .'<payid>PPAY</payid><amt>15.000</amt>'
                .'<udf1></udf1><udf2></udf2><udf3></udf3><udf4></udf4><udf5></udf5>'
            ),
        ]);

        $result = $this->refundService->refundPayment($transaction, 15.000);

        $this->assertEquals('CAPTURED', $result['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<action>2</action>')
                && str_contains($request->body(), '<amt>15.000</amt>')
                && str_contains($request->body(), '<trackid>REF-PARTIAL-001</trackid>');
        });
    }

    public function test_refund_creates_refund_transaction_record()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-RECORD-001',
            'amt' => '10.000',
            'udf1' => 'order_abc',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-RECORD-001',
            'amt' => '10.000',
        ]);

        $this->refundService->refundPayment($transaction);

        $this->assertDatabaseHas('knet_transactions', [
            'trackid' => 'REF-RECORD-001',
            'action' => 2,
            'amt' => '10.000',
            'udf1' => 'order_abc',
            'udf5' => 'TrackID',
            'original_transaction_id' => $transaction->id,
        ]);
    }

    public function test_refund_transaction_updated_on_success()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-TXUPD-001',
            'amt' => '20.000',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-TXUPD-001',
            'amt' => '20.000',
            'auth' => 'RFAUTH',
            'ref' => 'RFREF',
            'tranid' => 'RFTRAN',
            'payid' => 'RFPAY',
            'postdate' => '03081430',
        ]);

        $this->refundService->refundPayment($transaction);

        $refundTx = KnetTransaction::where('original_transaction_id', $transaction->id)->first();
        $this->assertNotNull($refundTx);
        $this->assertEquals('CAPTURED', $refundTx->result);
        $this->assertEquals('RFAUTH', $refundTx->auth);
        $this->assertEquals('RFREF', $refundTx->ref);
        $this->assertEquals('RFTRAN', $refundTx->tranid);
        $this->assertTrue((bool) $refundTx->paid);
    }

    public function test_refund_sends_correct_xml_payload()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-XML-001',
            'amt' => '30.000',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-XML-001',
            'amt' => '30.000',
        ]);

        $this->refundService->refundPayment($transaction);

        Http::assertSent(function ($request) {
            $body = $request->body();
            $expectedUrl = KnetApiMock::inquiryUrl().'?param=tranInit';

            return $request->url() === $expectedUrl
                && str_contains($body, '<id>'.KnetApiMock::transportId().'</id>')
                && str_contains($body, '<password>'.KnetApiMock::transportPassword().'</password>')
                && str_contains($body, '<action>2</action>')
                && str_contains($body, '<amt>30.000</amt>')
                && str_contains($body, '<trackid>REF-XML-001</trackid>')
                && str_contains($body, '<udf5>TrackID</udf5>');
        });
    }

    public function test_failed_refund_marks_refund_transaction_as_failed()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-FAIL-001',
            'amt' => '10.000',
        ]);

        KnetApiMock::fakeServerError();

        try {
            $this->refundService->refundPayment($transaction);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Failed to get response from KNET', $e->getMessage());
        }

        $refundTx = KnetTransaction::where('original_transaction_id', $transaction->id)->first();
        $this->assertNotNull($refundTx);
        $this->assertEquals('FAILED', $refundTx->result);
        $this->assertNotNull($refundTx->error_text);

        $transaction->refresh();
        $this->assertFalse($transaction->refunded);
    }

    public function test_refund_error_response_does_not_mark_as_refunded()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-ERR-001',
            'amt' => '10.000',
        ]);

        KnetApiMock::fakeRefundError('IPAY0100263', 'Transaction not found.');

        $result = $this->refundService->refundPayment($transaction);

        $this->assertEquals('ERROR', $result['result']);
        $this->assertEquals('IPAY0100263', $result['error_code']);
        $this->assertEquals('Transaction not found.', $result['error_message']);

        $transaction->refresh();
        $this->assertFalse($transaction->refunded);
        $this->assertNull($transaction->refunded_at);
    }

    public function test_refund_uses_transaction_amount_when_no_amount_provided()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-DEFAMT-001',
            'amt' => '75.250',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-DEFAMT-001',
            'amt' => '75.250',
        ]);

        $this->refundService->refundPayment($transaction);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<amt>75.250</amt>');
        });
    }

    public function test_refund_timeout_marks_refund_as_failed()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-TIMEOUT-001',
            'amt' => '5.000',
        ]);

        KnetApiMock::fakeTimeout();

        try {
            $this->refundService->refundPayment($transaction);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Failed to get response from KNET', $e->getMessage());
        }

        $refundTx = KnetTransaction::where('original_transaction_id', $transaction->id)->first();
        $this->assertEquals('FAILED', $refundTx->result);

        $transaction->refresh();
        $this->assertFalse($transaction->refunded);
    }

    public function test_refund_preserves_user_id_from_original_transaction()
    {
        $transaction = $this->createCapturedTransaction([
            'trackid' => 'REF-USER-001',
            'user_id' => 42,
            'amt' => '10.000',
        ]);

        KnetApiMock::fakeRefundSuccess([
            'trackid' => 'REF-USER-001',
            'amt' => '10.000',
        ]);

        $this->refundService->refundPayment($transaction);

        $refundTx = KnetTransaction::where('original_transaction_id', $transaction->id)->first();
        $this->assertEquals(42, $refundTx->user_id);
    }
}

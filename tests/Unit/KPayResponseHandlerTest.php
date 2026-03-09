<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Enums\Errors;
use Asciisd\Knet\Services\KPayResponseHandler;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Http\Request;

class KPayResponseHandlerTest extends TestCase
{
    private function makeHandler(array $transaction, array $requestData = []): KPayResponseHandler
    {
        $request = Request::create('/knet/response', 'POST', $requestData);

        return new KPayResponseHandler($transaction, $request);
    }

    public function test_captured_result_sets_paid_to_true()
    {
        $handler = $this->makeHandler(['result' => 'CAPTURED']);

        $data = $handler->toArray();

        $this->assertTrue($data['paid']);
    }

    public function test_non_captured_result_sets_paid_to_false()
    {
        $handler = $this->makeHandler(['result' => 'FAILED']);

        $data = $handler->toArray();

        $this->assertFalse($data['paid']);
    }

    public function test_has_errors_returns_false_when_no_error()
    {
        $handler = $this->makeHandler(['result' => 'CAPTURED']);

        $this->assertFalse($handler->hasErrors());
    }

    public function test_has_errors_returns_true_when_error_present()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'ErrorText' => 'Card declined', 'Error' => 'IPAY0100114']
        );

        $this->assertTrue($handler->hasErrors());
    }

    public function test_error_returns_description_when_enum_matches()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'ErrorText' => 'Some error', 'Error' => 'IPAY0100114']
        );

        $this->assertEquals(Errors::IPAY0100114->description(), $handler->error());
    }

    public function test_error_returns_raw_text_when_no_enum_match()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'ErrorText' => 'Custom error message', 'Error' => 'UNKNOWN_CODE']
        );

        $this->assertEquals('Custom error message', $handler->error());
    }

    public function test_error_code_returns_the_error_code()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100114', 'ErrorText' => 'err']
        );

        $this->assertEquals('IPAY0100114', $handler->errorCode());
    }

    public function test_error_enum_returns_enum_when_valid()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100114', 'ErrorText' => 'err']
        );

        $this->assertSame(Errors::IPAY0100114, $handler->errorEnum());
    }

    public function test_error_enum_returns_null_for_invalid_code()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'INVALID', 'ErrorText' => 'err']
        );

        $this->assertNull($handler->errorEnum());
    }

    public function test_is_duplicated_returns_true_for_duplicate_error()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100114', 'ErrorText' => 'Duplicate']
        );

        $this->assertTrue($handler->isDuplicated());
    }

    public function test_is_duplicated_returns_false_for_other_errors()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100055', 'ErrorText' => 'err']
        );

        $this->assertFalse($handler->isDuplicated());
    }

    public function test_is_invalid_payment_status_for_specific_error()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100055', 'ErrorText' => 'err']
        );

        $this->assertTrue($handler->isInvalidPaymentStatus());
    }

    public function test_error_on_unpaid_transaction_sets_result_to_failed()
    {
        $handler = $this->makeHandler(
            ['result' => 'PENDING'],
            ['error' => '1', 'Error' => 'IPAY0100001', 'ErrorText' => 'err']
        );

        $data = $handler->toArray();
        $this->assertEquals('FAILED', $data['result']);
    }

    public function test_error_on_paid_transaction_preserves_result()
    {
        $handler = $this->makeHandler(
            ['result' => 'CAPTURED'],
            ['error' => '1', 'Error' => 'IPAY0100001', 'ErrorText' => 'err']
        );

        $data = $handler->toArray();
        $this->assertEquals('CAPTURED', $data['result']);
    }

    public function test_to_string_returns_json()
    {
        $handler = $this->makeHandler(['result' => 'CAPTURED', 'trackid' => 'test']);

        $json = (string) $handler;
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertEquals('test', $decoded['trackid']);
    }

    public function test_make_factory_method()
    {
        $request = Request::create('/knet/response', 'POST');
        $handler = KPayResponseHandler::make(['result' => 'CAPTURED'], $request);

        $this->assertInstanceOf(KPayResponseHandler::class, $handler);
    }

    public function test_get_transaction_property()
    {
        $handler = $this->makeHandler([
            'result' => 'CAPTURED',
            'trackid' => 'track-test',
            'amt' => '25.000',
        ]);

        $this->assertEquals('track-test', $handler->getTransactionProperty('trackid'));
        $this->assertEquals('25.000', $handler->getTransactionProperty('amt'));
        $this->assertNull($handler->getTransactionProperty('nonexistent'));
    }

    public function test_rspcode_set_from_request_on_error()
    {
        $handler = $this->makeHandler(
            ['result' => 'FAILED'],
            ['error' => '1', 'Error' => 'IPAY0100001', 'ErrorText' => 'err', 'rspcode' => '05']
        );

        $data = $handler->toArray();
        $this->assertEquals('05', $data['rspcode']);
    }
}

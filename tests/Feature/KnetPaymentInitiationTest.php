<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class KnetPaymentInitiationTest extends TestCase
{
    private KnetPaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->setUpUsersTable();
        $this->paymentService = $this->app->make(KnetPaymentService::class);
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

    public function test_payment_url_targets_test_environment()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertStringContainsString('kpaytest.com.kw', $transaction->url);
        $this->assertStringContainsString('param=paymentInit', $transaction->url);
    }

    public function test_payment_url_contains_transport_id()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertStringContainsString(
            'tranportalId='.KnetApiMock::transportId(),
            $transaction->url
        );
    }

    public function test_payment_url_contains_response_and_error_urls()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertStringContainsString('responseURL=', $transaction->url);
        $this->assertStringContainsString('errorURL=', $transaction->url);
    }

    public function test_encrypted_trandata_is_decryptable()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 25.500, [
            'udf1' => 'order_999',
        ]);

        $url = $transaction->url;
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

        $this->assertArrayHasKey('trandata', $queryParams);

        $decrypted = KPayClient::decryptAES(
            urldecode($queryParams['trandata']),
            KnetApiMock::resourceKey()
        );
        parse_str($decrypted, $params);

        $this->assertEquals(KnetApiMock::transportId(), $params['id']);
        $this->assertEquals(KnetApiMock::transportPassword(), $params['password']);
        $this->assertEquals('1', $params['action']);
        $this->assertEquals('25.500', $params['amt']);
        $this->assertEquals($transaction->trackid, $params['trackid']);
        $this->assertEquals('order_999', $params['udf1']);
    }

    public function test_encrypted_params_include_currency_and_language()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 5.000);

        $url = $transaction->url;
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

        $decrypted = KPayClient::decryptAES(
            urldecode($queryParams['trandata']),
            KnetApiMock::resourceKey()
        );
        parse_str($decrypted, $params);

        $this->assertEquals('414', $params['currencycode']);
        $this->assertEquals('EN', $params['langid']);
    }

    public function test_transaction_stored_with_correct_amount()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 99.999);

        $this->assertEquals('99.999', $transaction->amt);
        $this->assertDatabaseHas('knet_transactions', [
            'amt' => '99.999',
            'user_id' => $user->id,
        ]);
    }

    public function test_transaction_starts_as_initiated()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertEquals('INITIATED', $transaction->result);
        $this->assertFalse((bool) $transaction->paid);
    }

    public function test_transaction_marked_as_non_live_in_debug_mode()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertFalse((bool) $transaction->livemode);
    }

    public function test_payment_with_all_udf_fields_encrypted()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 1.000, [
            'udf1' => 'val1',
            'udf2' => 'val2',
            'udf3' => 'val3',
            'udf4' => 'val4',
            'udf5' => 'val5',
        ]);

        $url = $transaction->url;
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

        $decrypted = KPayClient::decryptAES(
            urldecode($queryParams['trandata']),
            KnetApiMock::resourceKey()
        );
        parse_str($decrypted, $params);

        $this->assertEquals('val1', $params['udf1']);
        $this->assertEquals('val2', $params['udf2']);
        $this->assertEquals('val3', $params['udf3']);
        $this->assertEquals('val4', $params['udf4']);
        $this->assertEquals('val5', $params['udf5']);
    }

    public function test_custom_track_id_used_in_url()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000, [
            'trackid' => 'MY-CUSTOM-TRK-123',
        ]);

        $this->assertEquals('MY-CUSTOM-TRK-123', $transaction->trackid);

        $url = $transaction->url;
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

        $decrypted = KPayClient::decryptAES(
            urldecode($queryParams['trandata']),
            KnetApiMock::resourceKey()
        );
        parse_str($decrypted, $params);

        $this->assertEquals('MY-CUSTOM-TRK-123', $params['trackid']);
    }

    public function test_auto_generated_track_id_when_not_provided()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 10.000);

        $this->assertNotEmpty($transaction->trackid);
        $this->assertIsString($transaction->trackid);
    }

    public function test_minimum_amount_payment()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 0.001);

        $this->assertEquals('0.001', $transaction->amt);
    }

    public function test_large_amount_payment()
    {
        $user = $this->createUser();
        $transaction = $this->paymentService->createPayment($user, 9999.999);

        $this->assertEquals('9999.999', $transaction->amt);
    }

    public function test_multiple_payments_create_unique_transactions()
    {
        $user = $this->createUser();

        $tx1 = $this->paymentService->createPayment($user, 10.000, ['udf1' => 'order_1']);
        $tx2 = $this->paymentService->createPayment($user, 20.000, ['udf1' => 'order_2']);

        $this->assertNotEquals($tx1->id, $tx2->id);
        $this->assertNotEquals($tx1->trackid, $tx2->trackid);
        $this->assertNotEquals($tx1->url, $tx2->url);
        $this->assertEquals(2, KnetTransaction::count());
    }

    public function test_payment_url_uses_production_url_when_debug_off()
    {
        config(['knet.debug' => false]);
        $this->refreshKnetServices();
        $service = $this->app->make(KnetPaymentService::class);

        $user = $this->createUser();
        $transaction = $service->createPayment($user, 10.000);

        $this->assertStringContainsString('kpay.com.kw', $transaction->url);
        $this->assertStringNotContainsString('kpaytest', $transaction->url);
    }
}

<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Events\KnetPaymentFailed;
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\Events\KnetTransactionCreated;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class PaymentFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        $this->setUpUsersTable();
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

    public function test_full_successful_payment_flow()
    {
        Event::fake([
            KnetPaymentSucceeded::class,
            KnetTransactionCreated::class,
        ]);

        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 25.500, [
            'udf1' => 'order_123',
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertEquals('25.500', $transaction->amt);
        $this->assertEquals('order_123', $transaction->udf1);
        $this->assertEquals('INITIATED', $transaction->result);
        $this->assertNotNull($transaction->url);
        $this->assertStringContainsString('paymentInit', $transaction->url);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_FLOW_1',
            'trackid' => $transaction->trackid,
            'result' => 'CAPTURED',
            'auth' => 'AUTH_FLOW',
            'ref' => 'REF_FLOW',
            'tranid' => 'TRAN_FLOW',
            'amt' => '25.500',
            'postdate' => now()->format('Ymd'),
            'udf1' => 'order_123',
        ]);

        $response = $this->post(route('knet.response.store'), [
            'trandata' => $trandata,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('REDIRECT=', $response->getContent());

        $transaction->refresh();
        $this->assertEquals('CAPTURED', $transaction->result);
        $this->assertTrue((bool) $transaction->paid);
        $this->assertEquals('AUTH_FLOW', $transaction->auth);
        $this->assertEquals($user->id, $transaction->user_id);

        Event::assertDispatched(KnetPaymentSucceeded::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
    }

    public function test_full_failed_payment_flow()
    {
        Event::fake([KnetPaymentFailed::class, KnetTransactionCreated::class]);

        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 10.000);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_FAIL_FLOW',
            'trackid' => $transaction->trackid,
            'result' => 'NOT CAPTURED',
            'amt' => '10.000',
            'error_text' => 'Insufficient funds',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction->refresh();
        $this->assertEquals('NOT CAPTURED', $transaction->result);
        $this->assertFalse((bool) $transaction->paid);
        $this->assertFalse($transaction->isRefundable());

        Event::assertDispatched(KnetPaymentFailed::class);
        Event::assertNotDispatched(KnetPaymentSucceeded::class);
    }

    public function test_payment_creates_transaction_with_correct_user()
    {
        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 50.000);

        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertDatabaseHas('knet_transactions', [
            'user_id' => $user->id,
            'amt' => '50.000',
        ]);
    }

    public function test_payment_url_contains_encrypted_data()
    {
        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 30.000);

        $this->assertNotNull($transaction->url);
        $this->assertStringContainsString('trandata=', $transaction->url);
        $this->assertStringContainsString('tranportalId=', $transaction->url);
        $this->assertStringContainsString('responseURL=', $transaction->url);
        $this->assertStringContainsString('errorURL=', $transaction->url);
    }

    public function test_payment_with_all_udf_fields()
    {
        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 5.000, [
            'udf1' => 'field_1',
            'udf2' => 'field_2',
            'udf3' => 'field_3',
            'udf4' => 'field_4',
            'udf5' => 'field_5',
        ]);

        $this->assertEquals('field_1', $transaction->udf1);
        $this->assertEquals('field_2', $transaction->udf2);
        $this->assertEquals('field_3', $transaction->udf3);
        $this->assertEquals('field_4', $transaction->udf4);
        $this->assertEquals('field_5', $transaction->udf5);
    }

    public function test_payment_with_custom_trackid()
    {
        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 1.000, [
            'trackid' => 'custom-track-id-999',
        ]);

        $this->assertEquals('custom-track-id-999', $transaction->trackid);
    }

    public function test_captured_transaction_is_refundable()
    {
        Event::fake();

        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 20.000);

        $this->assertFalse($transaction->isRefundable());

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_REFUND_TEST',
            'trackid' => $transaction->trackid,
            'result' => 'CAPTURED',
            'auth' => 'AUTH_REF',
            'amt' => '20.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $transaction->refresh();
        $this->assertTrue($transaction->isCaptured());
        $this->assertTrue($transaction->isRefundable());
    }

    public function test_handle_redirect_after_successful_payment()
    {
        Event::fake();

        $user = $this->createUser();

        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 15.000);

        $trandata = $this->encryptPayload([
            'paymentid' => 'PAY_HANDLE',
            'trackid' => $transaction->trackid,
            'result' => 'CAPTURED',
            'amt' => '15.000',
        ]);

        $this->post(route('knet.response.store'), ['trandata' => $trandata]);

        $handleResponse = $this->post(route('knet.handle'));
        $handleResponse->assertRedirect(config('knet.redirect_url'));
    }

    public function test_livemode_reflects_debug_config()
    {
        $user = $this->createUser();

        config(['knet.debug' => true]);
        $this->app->forgetInstance(KnetPaymentService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Config\KnetConfig::class);
        $service = $this->app->make(KnetPaymentService::class);
        $transaction = $service->createPayment($user, 5.000);
        $this->assertFalse((bool) $transaction->livemode);

        config(['knet.debug' => false]);
        $this->app->forgetInstance(KnetPaymentService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Config\KnetConfig::class);
        $service = $this->app->make(KnetPaymentService::class);
        $transaction2 = $service->createPayment($user, 5.000);
        $this->assertTrue((bool) $transaction2->livemode);
    }
}

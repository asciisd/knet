<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\CashierCore\Enums\PaymentStatus;
use Asciisd\CashierCore\Exceptions\PaymentProcessingException;
use Asciisd\Knet\Cashier\KnetProcessor;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Tests\Mocks\CashierTestUser;
use Asciisd\Knet\Tests\TestCase;
use Mockery;

class KnetProcessorTest extends TestCase
{
    public function test_prepare_charge_data_injects_user_model_and_kwd_currency(): void
    {
        $customer = new CashierTestUser(['id' => 7, 'name' => 'Test User', 'email' => 'test@example.com']);

        $prepared = (new KnetProcessor)->prepareChargeData($customer, 'knet', [
            'amount' => 1000,
            'currency' => 'USD',
        ]);

        $this->assertSame($customer, $prepared['user']);
        $this->assertSame('KWD', $prepared['currency']);
        $this->assertSame(1000, $prepared['amount']);
    }

    public function test_charge_requires_a_user_model(): void
    {
        $this->expectException(PaymentProcessingException::class);
        $this->expectExceptionMessage('User is required for Knet payments');

        (new KnetProcessor)->charge(['amount' => 1000]);
    }

    public function test_charge_creates_a_knet_payment_and_surfaces_the_redirect(): void
    {
        $user = new CashierTestUser(['id' => 7, 'name' => 'Test User', 'email' => 'test@example.com']);

        $knetTransaction = (new KnetTransaction)->forceFill([
            'trackid' => 'TRACK123',
            'amt' => 10.0,
            'result' => 'INITIATED',
            'url' => 'https://kpaytest.com.kw/kpg/paymentpage.htm?PaymentID=1',
        ]);

        $service = Mockery::mock(KnetPaymentService::class);
        $service->shouldReceive('createPayment')
            ->once()
            ->withArgs(function ($chargedUser, $amount, $options) use ($user) {
                return $chargedUser === $user
                    && $amount === 10.0
                    && $options['udf1'] === 7
                    && $options['udf2'] === 'test@example.com'
                    && $options['udf5'] === 'Deposit DEP-1';
            })
            ->andReturn($knetTransaction);

        $this->app->instance(KnetPaymentService::class, $service);

        $result = (new KnetProcessor)->charge([
            'amount' => 1000,
            'user' => $user,
            'description' => 'Deposit: DEP-1!',
            'metadata' => [
                'user_id' => 7,
                'user_email' => 'test@example.com',
            ],
        ]);

        $this->assertSame('TRACK123', $result->transactionId);
        $this->assertSame(PaymentStatus::RequiresAction, $result->status);
        $this->assertSame(1000, $result->amount);
        $this->assertSame('KWD', $result->currency);
        $this->assertSame('https://kpaytest.com.kw/kpg/paymentpage.htm?PaymentID=1', $result->metadata['redirect_url']);
    }

    public function test_extracts_webhook_transaction_id_from_trackid(): void
    {
        $processor = new KnetProcessor;

        $this->assertSame('TRACK123', $processor->extractWebhookTransactionId(['trackid' => 'TRACK123']));
        $this->assertNull($processor->extractWebhookTransactionId(['trackid' => '']));
        $this->assertNull($processor->extractWebhookTransactionId([]));
    }

    public function test_processor_identity_and_features(): void
    {
        $processor = new KnetProcessor;

        $this->assertSame('knet', $processor->getName());
        $this->assertTrue($processor->supports('charge'));
        $this->assertTrue($processor->supports('refund'));
        $this->assertTrue($processor->supports('inquiry'));
        $this->assertFalse($processor->supports('hosted_page'));
    }
}

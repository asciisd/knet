<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Services\KnetPaymentInitiationService;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * An application with no KNET credentials must still boot and resolve.
 *
 * Route enumeration resolves controllers, and controllers pull their service
 * dependencies out of the container — so a service that reads credentials
 * while being constructed makes `route:list`, `wayfinder:generate` and the
 * whole asset build fail in any environment KNET is not set up in.
 */
class UnconfiguredBootTest extends TestCase
{
    public function test_payment_services_resolve_without_credentials()
    {
        Config::set('knet.transport.id', '');
        Config::set('knet.transport.password', '');
        Config::set('knet.resource_key', '');

        $this->app->forgetInstance(KnetConfig::class);

        $this->assertInstanceOf(
            KnetPaymentInitiationService::class,
            $this->app->make(KnetPaymentInitiationService::class)
        );

        $this->assertInstanceOf(
            KnetPaymentService::class,
            $this->app->make(KnetPaymentService::class)
        );
    }

    public function test_taking_a_payment_without_credentials_still_fails_loudly()
    {
        Config::set('knet.transport.id', '');
        $this->app->forgetInstance(KnetConfig::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet transport ID is required');

        $user = new class extends Model {};

        $this->app->make(KnetPaymentInitiationService::class)
            ->createPayment($user, 10.0);
    }
}

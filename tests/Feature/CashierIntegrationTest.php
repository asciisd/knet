<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\CashierCore\CashierCoreServiceProvider;
use Asciisd\CashierCore\Connections\ConnectionRegistry;
use Asciisd\Knet\Cashier\KnetProcessor;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class CashierIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            CashierCoreServiceProvider::class,
        ]);
    }

    public function test_registers_the_knet_driver_in_the_cashier_core_driver_map(): void
    {
        $this->assertSame(KnetProcessor::class, config('cashier-core.drivers.knet'));
    }

    public function test_keeps_the_bundled_cashier_core_drivers_alongside_its_own(): void
    {
        $drivers = config('cashier-core.drivers');

        $this->assertArrayHasKey('knet', $drivers);
        $this->assertArrayHasKey('manual', $drivers);
    }

    public function test_resolves_a_knet_connection_to_the_processor(): void
    {
        config()->set('cashier-core.connections.knet', ['driver' => 'knet']);

        $processor = $this->app->make(ConnectionRegistry::class)->get('knet');

        $this->assertInstanceOf(KnetProcessor::class, $processor);
    }

    public function test_registers_the_package_routes_by_default(): void
    {
        $this->assertTrue(Route::has('knet.response.store'));
        $this->assertTrue(Route::has('knet.handle'));
        $this->assertTrue(Route::has('knet.error'));
    }
}

<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Knet;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class KnetIgnoreRoutesTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        Knet::ignoreRoutes();
    }

    protected function tearDown(): void
    {
        Knet::$registersRoutes = true;

        parent::tearDown();
    }

    public function test_registers_no_routes_when_ignore_routes_was_called(): void
    {
        $this->assertFalse(Route::has('knet.response.store'));
        $this->assertFalse(Route::has('knet.handle'));
        $this->assertFalse(Route::has('knet.error'));
    }
}

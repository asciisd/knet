<?php

namespace Asciisd\Knet\Tests;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Providers\KnetServiceProvider;
use Asciisd\Knet\Tests\Fixtures\User;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            KnetServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        KnetTransaction::useCustomerModel(User::class);
    }
}

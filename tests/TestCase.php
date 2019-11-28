<?php

namespace Asciisd\Knet\Tests;


use Asciisd\Knet\Providers\KnetServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [KnetServiceProvider::class];
    }
}

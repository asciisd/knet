<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Knet;
use Asciisd\Knet\Tests\TestCase;

class KnetIgnoreMigrationsTest extends TestCase
{
    protected bool $loadsPackageMigrations = false;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        Knet::ignoreMigrations();
    }

    protected function tearDown(): void
    {
        Knet::$runsMigrations = true;

        parent::tearDown();
    }

    public function test_loads_no_migration_paths_when_ignore_migrations_was_called(): void
    {
        $packageMigrations = realpath(__DIR__.'/../../database/migrations');

        $loaded = array_map(
            fn (string $path) => realpath($path),
            $this->app->make('migrator')->paths(),
        );

        $this->assertNotContains($packageMigrations, $loaded);
    }
}

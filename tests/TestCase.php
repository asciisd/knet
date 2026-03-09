<?php

namespace Asciisd\Knet\Tests;

use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Providers\KnetServiceProvider;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            KnetServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // KNET test credentials (loaded from phpunit.xml.dist env vars)
        $app['config']->set('knet.transport.id', KnetApiMock::transportId());
        $app['config']->set('knet.transport.password', KnetApiMock::transportPassword());
        $app['config']->set('knet.resource_key', KnetApiMock::resourceKey());
        $app['config']->set('knet.debug', true);
        $app['config']->set('knet.debug_hex_conversion', true);
        $app['config']->set('knet.debug_response_data', true);
        $app['config']->set('knet.debug_validation_failures', true);

        // Setup routes
        $app['config']->set('knet.response_url', '/knet/response');
        $app['config']->set('knet.error_url', '/knet/error');
        $app['config']->set('knet.redirect_url', '/dashboard');
    }

    protected function createTestUser(): \Illuminate\Foundation\Auth\User
    {
        return new class extends \Illuminate\Foundation\Auth\User
        {
            protected $fillable = ['id', 'name', 'email'];

            public function __construct(array $attributes = [])
            {
                parent::__construct(array_merge([
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ], $attributes));
            }
        };
    }

    protected function setUpUsersTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    protected function createDbUser(): \Illuminate\Foundation\Auth\User
    {
        return \Illuminate\Foundation\Auth\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    protected function encryptPayload(array $data): string
    {
        return urldecode(KPayClient::encryptAES(
            http_build_query($data),
            KnetApiMock::resourceKey()
        ));
    }

    /**
     * Rebuild service container bindings after config changes
     */
    protected function refreshKnetServices(): void
    {
        $this->app->forgetInstance(\Asciisd\Knet\Services\KnetPaymentService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Services\KnetInquiryService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Services\KnetRefundService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Services\KnetPaymentInitiationService::class);
        $this->app->forgetInstance(\Asciisd\Knet\Config\KnetConfig::class);
    }

    protected function createMockKnetRequest(array $data = []): \Illuminate\Http\Request
    {
        $defaultData = [
            'trandata' => 'abcdef123456789012345678901234567890abcdef123456789012345678901234',
            'other_field' => 'some_value',
        ];

        $requestData = array_merge($defaultData, $data);

        return \Illuminate\Http\Request::create('/knet/response', 'POST', $requestData);
    }

    protected function createMockKnetRequestWithContent(string $content): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create('/knet/response', 'POST');
        $request->initialize([], [], [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $content);

        return $request;
    }
}

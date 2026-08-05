<?php

namespace Asciisd\Knet\Tests;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Providers\KnetServiceProvider;
use Asciisd\Knet\Services\KnetInquiryService;
use Asciisd\Knet\Services\KnetPaymentInitiationService;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Services\KnetRefundService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Whether setUp() loads the package migrations manually. Flag tests that
     * assert on Knet::$runsMigrations behavior turn this off so the manual
     * load doesn't mask the service provider's decision.
     */
    protected bool $loadsPackageMigrations = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        if ($this->loadsPackageMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
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

    protected function createTestUser(): User
    {
        return new class extends User
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

    protected function createDbUser(): User
    {
        return User::create([
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
        $this->app->forgetInstance(KnetPaymentService::class);
        $this->app->forgetInstance(KnetInquiryService::class);
        $this->app->forgetInstance(KnetRefundService::class);
        $this->app->forgetInstance(KnetPaymentInitiationService::class);
        $this->app->forgetInstance(KnetConfig::class);
    }

    protected function createMockKnetRequest(array $data = []): Request
    {
        $defaultData = [
            'trandata' => 'abcdef123456789012345678901234567890abcdef123456789012345678901234',
            'other_field' => 'some_value',
        ];

        $requestData = array_merge($defaultData, $data);

        return Request::create('/knet/response', 'POST', $requestData);
    }

    protected function createMockKnetRequestWithContent(string $content): Request
    {
        $request = Request::create('/knet/response', 'POST');
        $request->initialize([], [], [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $content);

        return $request;
    }
}

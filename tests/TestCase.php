<?php

namespace Asciisd\Knet\Tests;

use Asciisd\Knet\Providers\KnetServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
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
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Knet configuration for testing
        $app['config']->set('knet.transport.id', 'test_transport_id');
        $app['config']->set('knet.transport.password', 'test_transport_password');
        $app['config']->set('knet.resource_key', 'test_resource_key');
        $app['config']->set('knet.debug', true);
        $app['config']->set('knet.debug_hex_conversion', true);
        $app['config']->set('knet.debug_response_data', true);
        $app['config']->set('knet.debug_validation_failures', true);

        // Setup routes
        $app['config']->set('knet.response_url', '/knet/response');
        $app['config']->set('knet.error_url', '/knet/error');
        $app['config']->set('knet.redirect_url', '/dashboard');
    }

    /**
     * Create a test user for payment testing
     */
    protected function createTestUser(): \Illuminate\Foundation\Auth\User
    {
        return new class extends \Illuminate\Foundation\Auth\User {
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

    /**
     * Create a mock request with KNet response data
     */
    protected function createMockKnetRequest(array $data = []): \Illuminate\Http\Request
    {
        $defaultData = [
            'trandata' => 'abcdef123456789012345678901234567890abcdef123456789012345678901234',
            'other_field' => 'some_value'
        ];

        $requestData = array_merge($defaultData, $data);

        return \Illuminate\Http\Request::create('/knet/response', 'POST', $requestData);
    }

    /**
     * Create a mock request with raw content
     */
    protected function createMockKnetRequestWithContent(string $content): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create('/knet/response', 'POST');
        $request->initialize([], [], [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $content);
        
        return $request;
    }
} 
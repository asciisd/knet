<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Tests\TestCase;

class KnetConfigTest extends TestCase
{
    private function validConfig(array $overrides = []): array
    {
        return array_merge([
            'transport' => [
                'id' => 'test_id',
                'password' => 'test_pass',
            ],
            'resource_key' => 'test_key',
            'debug' => false,
            'development_url' => 'https://kpaytest.com.kw',
            'production_url' => 'https://kpay.com.kw',
            'development_inquiry_url' => 'https://kpaytest.com.kw/inquiry',
            'production_inquiry_url' => 'https://kpay.com.kw/inquiry',
        ], $overrides);
    }

    public function test_valid_config_creates_instance()
    {
        $config = new KnetConfig($this->validConfig());

        $this->assertEquals('test_id', $config->getTransportId());
        $this->assertEquals('test_pass', $config->getTransportPassword());
        $this->assertEquals('test_key', $config->getResourceKey());
    }

    public function test_missing_transport_id_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet transport ID is required');

        (new KnetConfig($this->validConfig([
            'transport' => ['id' => '', 'password' => 'test_pass'],
        ])))->getTransportId();
    }

    public function test_missing_transport_password_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet transport password is required');

        (new KnetConfig($this->validConfig([
            'transport' => ['id' => 'test_id', 'password' => ''],
        ])))->getTransportPassword();
    }

    public function test_missing_resource_key_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet resource key is required');

        (new KnetConfig($this->validConfig(['resource_key' => ''])))->getResourceKey();
    }

    public function test_missing_development_url_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet development URL is required');

        (new KnetConfig($this->validConfig(['development_url' => ''])))->getPaymentUrl();
    }

    public function test_missing_production_url_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Knet production URL is required');

        (new KnetConfig($this->validConfig(['production_url' => ''])))->getPaymentUrl();
    }

    public function test_debug_mode_returns_boolean()
    {
        $config = new KnetConfig($this->validConfig(['debug' => true]));
        $this->assertTrue($config->isDebugMode());

        $config = new KnetConfig($this->validConfig(['debug' => false]));
        $this->assertFalse($config->isDebugMode());
    }

    public function test_payment_url_returns_development_url_in_debug_mode()
    {
        $config = new KnetConfig($this->validConfig(['debug' => true]));

        $this->assertEquals('https://kpaytest.com.kw', $config->getPaymentUrl());
    }

    public function test_payment_url_returns_production_url_when_not_debug()
    {
        // 'testing' env is neither 'local' nor debug, so production URL is returned
        $config = new KnetConfig($this->validConfig(['debug' => false]));

        $this->assertEquals('https://kpay.com.kw', $config->getPaymentUrl());
    }

    public function test_payment_url_returns_development_url_in_local_env()
    {
        $this->app['env'] = 'local';
        $config = new KnetConfig($this->validConfig(['debug' => false]));

        $this->assertEquals('https://kpaytest.com.kw', $config->getPaymentUrl());
    }

    public function test_inquiry_url_returns_development_url_in_debug_mode()
    {
        $config = new KnetConfig($this->validConfig(['debug' => true]));

        $this->assertEquals('https://kpaytest.com.kw/inquiry', $config->getInquiryUrl());
    }

    public function test_inquiry_url_returns_production_url_when_not_debug()
    {
        // 'testing' env is neither 'local' nor debug, so production URL is returned
        $config = new KnetConfig($this->validConfig(['debug' => false]));

        $this->assertEquals('https://kpay.com.kw/inquiry', $config->getInquiryUrl());
    }

    /*
     * The regression this class exists to prevent: the container resolves
     * KnetConfig as a singleton, so anything that merely resolves a KNET
     * controller built it — `route:list`, Wayfinder's route enumeration during
     * an asset build, a CI job with a placeholder .env. Validating in the
     * constructor turned "no KNET credentials here" into "the application
     * cannot boot".
     */
    public function test_an_unconfigured_config_can_be_constructed_and_inspected()
    {
        $config = new KnetConfig($this->validConfig([
            'transport' => ['id' => '', 'password' => ''],
            'resource_key' => '',
        ]));

        $this->assertFalse($config->isConfigured());
        $this->assertSame('EN', $config->getLanguage());
        $this->assertSame(414, $config->getCurrency());
    }

    public function test_is_configured_is_true_when_credentials_are_present()
    {
        $this->assertTrue((new KnetConfig($this->validConfig()))->isConfigured());
    }
}

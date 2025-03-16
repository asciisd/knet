<?php

namespace Asciisd\Knet\Config;

use Illuminate\Support\Facades\App;

class KnetConfig
{
    public function __construct(private readonly array $config)
    {
        $this->validateConfig();
    }

    public function getTransportId(): string
    {
        return $this->config['transport']['id'];
    }

    public function getTransportPassword(): string
    {
        return $this->config['transport']['password'];
    }

    public function getResourceKey(): string
    {
        return $this->config['resource_key'];
    }

    public function isDebugMode(): bool
    {
        return (bool) $this->config['debug'];
    }

    public function getPaymentUrl(): string
    {
        if ($this->isDebugMode() || App::environment('local')) {
            return $this->config['development_url'];
        }

        return $this->config['production_url'];
    }

    public function getInquiryUrl(): string
    {
        if ($this->isDebugMode() || App::environment('local')) {
            return $this->config['development_inquiry_url'];
        }

        return $this->config['production_inquiry_url'];
    }

    private function validateConfig(): void
    {
        if (empty($this->config['transport']['id'])) {
            throw new \InvalidArgumentException('Knet transport ID is required');
        }

        if (empty($this->config['transport']['password'])) {
            throw new \InvalidArgumentException('Knet transport password is required');
        }

        if (empty($this->config['resource_key'])) {
            throw new \InvalidArgumentException('Knet resource key is required');
        }

        if (empty($this->config['development_url'])) {
            throw new \InvalidArgumentException('Knet development URL is required');
        }

        if (empty($this->config['production_url'])) {
            throw new \InvalidArgumentException('Knet production URL is required');
        }
    }
}

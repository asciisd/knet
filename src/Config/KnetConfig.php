<?php

namespace Asciisd\Knet\Config;

use Illuminate\Support\Facades\App;

/**
 * Validation is deliberately deferred to the first read of a credential rather
 * than run in the constructor.
 *
 * The container binds this as a singleton, so it is built the moment anything
 * resolves it — and plenty of things resolve a controller without ever
 * intending to talk to KNET: `route:list`, Wayfinder's route enumeration during
 * an asset build, IDE helper generation, a CI job with a placeholder .env.
 * Throwing at construction turned "KNET has no credentials here" into "this
 * application cannot boot", which is how an unconfigured CI environment ended
 * up unable to build its frontend.
 *
 * The guarantee callers actually need is that no request reaches KNET with
 * missing credentials, and that still holds: every getter that exposes one
 * validates first, with the same messages as before.
 */
class KnetConfig
{
    private bool $validated = false;

    public function __construct(private readonly array $config) {}

    public function getTransportId(): string
    {
        $this->ensureConfigured();

        return $this->config['transport']['id'];
    }

    public function getTransportPassword(): string
    {
        $this->ensureConfigured();

        return $this->config['transport']['password'];
    }

    public function getResourceKey(): string
    {
        $this->ensureConfigured();

        return $this->config['resource_key'];
    }

    public function isDebugMode(): bool
    {
        return (bool) ($this->config['debug'] ?? false);
    }

    public function getLanguage(): string
    {
        return $this->config['language'] ?? 'EN';
    }

    public function getCurrency(): int
    {
        return (int) ($this->config['currency'] ?? 414);
    }

    public function getResponseUrl(): string
    {
        return url($this->config['response_url'] ?? '/knet/response');
    }

    public function getErrorUrl(): string
    {
        return url($this->config['error_url'] ?? '/knet/error');
    }

    public function getPaymentUrl(): string
    {
        $this->ensureConfigured();

        if ($this->isDebugMode() || App::environment('local')) {
            return $this->config['development_url'];
        }

        return $this->config['production_url'];
    }

    public function getInquiryUrl(): string
    {
        $this->ensureConfigured();

        if ($this->isDebugMode() || App::environment('local')) {
            return $this->config['development_inquiry_url'];
        }

        return $this->config['production_inquiry_url'];
    }

    /**
     * Assert the credentials are present, once per instance.
     *
     * @throws \InvalidArgumentException
     */
    public function ensureConfigured(): void
    {
        if ($this->validated) {
            return;
        }

        $this->validateConfig();

        $this->validated = true;
    }

    /**
     * Whether this config carries everything needed to reach KNET.
     *
     * Lets a host check the posture — a health command, a payment-method
     * listing — without having to catch an exception to find out.
     */
    public function isConfigured(): bool
    {
        try {
            $this->ensureConfigured();

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
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

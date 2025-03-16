<?php

namespace Asciisd\Knet\Tests\Integration;

use Asciisd\Knet\Exceptions\PaymentActionRequired;
use Asciisd\Knet\KPayManager;
use Asciisd\Knet\KnetTransaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery;

class InquiryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock HTTP responses for inquiry tests
        Http::fake([
            '*tranPipe.htm*' => Http::response($this->getMockInquiryResponse(), 200),
        ]);
    }
    
    /** @test */
    public function it_can_perform_transaction_inquiry()
    {
        $trackid = 'test-inquiry-' . time();
        $amount = 100;
        
        $result = KPayManager::inquiry($amount, $trackid);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('CAPTURED', $result['result']);
    }
    
    /** @test */
    public function it_can_perform_inquiry_with_existing_transaction()
    {
        $user = $this->createCustomer('inquiry_test_user');
        
        try {
            $transaction = $user->pay(100);
        } catch (PaymentActionRequired $e) {
            $transaction = $e->transaction;
        }
        
        $result = KPayManager::inquiry($transaction->rawAmount(), $transaction->trackid);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('CAPTURED', $result['result']);
    }
    
    /** @test */
    public function it_uses_correct_inquiry_url_based_on_environment()
    {
        // Test development URL
        Config::set('knet.debug', true);
        $result = KPayManager::inquiry(100, 'test-track-id');
        $this->assertStringContainsString(config('knet.development_inquiry_url'), Http::recorded()[0]->url());
        
        // Test production URL
        Config::set('knet.debug', false);
        $result = KPayManager::inquiry(100, 'test-track-id');
        $this->assertStringContainsString(config('knet.production_inquiry_url'), Http::recorded()[1]->url());
    }
    
    /** @test */
    public function it_handles_inquiry_error_responses()
    {
        // Mock error response
        Http::fake([
            '*tranPipe.htm*' => Http::response($this->getMockInquiryErrorResponse(), 200),
        ]);
        
        $result = KPayManager::inquiry(100, 'invalid-track-id');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEquals('CAPTURED', $result['result'] ?? '');
    }
    
    /**
     * Get a mock successful inquiry response
     */
    private function getMockInquiryResponse(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <response>
            <result>CAPTURED</result>
            <auth>123456</auth>
            <ref>789012</ref>
            <avr>N</avr>
            <postdate>0519</postdate>
            <tranid>123456789012</tranid>
            <payid>9876543210</payid>
            <udf5>TrackID</udf5>
            <trackid>test-inquiry-123456</trackid>
            <amt>100.000</amt>
        </response>';
    }
    
    /**
     * Get a mock error inquiry response
     */
    private function getMockInquiryErrorResponse(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <response>
            <error>Invalid Track ID</error>
            <error_text>The provided track ID does not exist</error_text>
        </response>';
    }
} 
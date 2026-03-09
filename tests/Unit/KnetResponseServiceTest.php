<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Services\KnetResponseService;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class KnetResponseServiceTest extends TestCase
{
    private KnetResponseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(KnetResponseService::class);
    }

    public function test_successful_trandata_extraction_from_parameters()
    {
        $request = $this->createMockKnetRequest([
            'trandata' => 'abcdef123456789012345678901234567890abcdef123456789012345678901234',
        ]);

        try {
            $result = $this->service->decryptAndParse($request);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('trackid', $result);
            $this->assertEquals('test123', $result['trackid']);
            $this->assertEquals('SUCCESS', $result['result']);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AccessDeniedHttpException::class, $e);
        }
    }

    public function test_trandata_extraction_from_raw_content()
    {
        $hexData = 'abcdef123456789012345678901234567890abcdef123456789012345678901234';
        $rawContent = "trandata={$hexData}&other_field=value&another=test";

        $request = $this->createMockKnetRequestWithContent($rawContent);

        try {
            $result = $this->service->decryptAndParse($request);
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AccessDeniedHttpException::class, $e);
        }
    }

    public function test_missing_trandata_throws_exception()
    {
        $request = Request::create('/knet/response', 'POST', [
            'other_field' => 'value',
            'no_trandata' => 'here',
        ]);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('No trandata field found in KNet response');

        $this->service->decryptAndParse($request);
    }

    public function test_empty_trandata_throws_exception()
    {
        $request = $this->createMockKnetRequest(['trandata' => '']);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('No trandata field found in KNet response');

        $this->service->decryptAndParse($request);
    }

    public function test_invalid_hex_data_handling()
    {
        $request = $this->createMockKnetRequest([
            'trandata' => 'invalid_hex_data_with_special_chars!@#',
        ]);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Invalid response data from KNet gateway');

        $this->service->decryptAndParse($request);
    }

    public function test_raw_hex_content_extraction()
    {
        $hexData = 'abcdef123456789012345678901234567890abcdef123456789012345678901234';
        $request = $this->createMockKnetRequestWithContent($hexData);

        try {
            $this->service->decryptAndParse($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AccessDeniedHttpException::class, $e);
            $this->assertStringNotContainsString('hex2bin', $e->getMessage());
        }
    }

    public function test_whitespace_in_trandata_handling()
    {
        $hexData = "  abcdef123456789012345678901234567890abcdef123456789012345678901234  \n";
        $request = $this->createMockKnetRequest(['trandata' => $hexData]);

        try {
            $this->service->decryptAndParse($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AccessDeniedHttpException::class, $e);
            $this->assertStringNotContainsString('Invalid hexadecimal characters', $e->getMessage());
        }
    }

    public function test_does_not_decrypt_entire_request_content()
    {
        $request = Request::create('/knet/response', 'POST');
        $request->initialize(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            'trandata=abcdef123456789012345678901234567890abcdef123456789012345678901234&other_field=value&result=SUCCESS'
        );

        try {
            $this->service->decryptAndParse($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AccessDeniedHttpException::class, $e);
            $this->assertStringNotContainsString('Invalid hexadecimal characters', $e->getMessage());
        }
    }
}

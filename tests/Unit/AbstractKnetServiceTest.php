<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Asciisd\Knet\Services\AbstractKnetService;
use Asciisd\Knet\Tests\TestCase;

class AbstractKnetServiceTest extends TestCase
{
    private function makeService(): AbstractKnetService
    {
        $config = new KnetConfig([
            'transport' => ['id' => 'test_id', 'password' => 'test_pass'],
            'resource_key' => 'test_key',
            'debug' => true,
            'development_url' => 'https://kpaytest.com.kw',
            'production_url' => 'https://kpay.com.kw',
            'development_inquiry_url' => 'https://kpaytest.com.kw/inquiry',
            'production_inquiry_url' => 'https://kpay.com.kw/inquiry',
        ]);

        $repository = $this->createMock(KnetTransactionRepository::class);

        return new class($config, $repository) extends AbstractKnetService {
            public function publicFormatAmount(float $amount): string
            {
                return $this->formatAmount($amount);
            }

            public function publicBuildInquiryXml(float|string $amount, string $trackid, string $action): string
            {
                return $this->buildInquiryXml($amount, $trackid, $action);
            }

            public function publicParseResponse(string $output): array
            {
                return $this->parseResponse($output);
            }

            public function publicNormalizeResponse(array $response): array
            {
                return $this->normalizeResponse($response);
            }
        };
    }

    public function test_format_amount_with_three_decimals()
    {
        $service = $this->makeService();

        $this->assertEquals('10.000', $service->publicFormatAmount(10));
        $this->assertEquals('25.500', $service->publicFormatAmount(25.5));
        $this->assertEquals('0.250', $service->publicFormatAmount(0.25));
        $this->assertEquals('99.999', $service->publicFormatAmount(99.999));
        $this->assertEquals('1000.000', $service->publicFormatAmount(1000));
    }

    public function test_format_amount_rounds_to_three_decimals()
    {
        $service = $this->makeService();

        $this->assertEquals('10.124', $service->publicFormatAmount(10.1235));
        $this->assertEquals('0.001', $service->publicFormatAmount(0.0005));
    }

    public function test_build_inquiry_xml_structure()
    {
        $service = $this->makeService();

        $xml = $service->publicBuildInquiryXml(25.500, 'track-123', '8');

        $this->assertStringContainsString('<request>', $xml);
        $this->assertStringContainsString('</request>', $xml);
        $this->assertStringContainsString('<id>test_id</id>', $xml);
        $this->assertStringContainsString('<password>test_pass</password>', $xml);
        $this->assertStringContainsString('<action>8</action>', $xml);
        $this->assertStringContainsString('<amt>25.500</amt>', $xml);
        $this->assertStringContainsString('<transid>track-123</transid>', $xml);
        $this->assertStringContainsString('<trackid>track-123</trackid>', $xml);
        $this->assertStringContainsString('<udf5>TrackID</udf5>', $xml);
    }

    public function test_parse_response_with_json()
    {
        $service = $this->makeService();

        $json = json_encode([
            'result' => 'CAPTURED',
            'trackid' => 'track-1',
            'auth' => 'AUTH123',
        ]);

        $result = $service->publicParseResponse($json);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('track-1', $result['trackid']);
        $this->assertEquals('AUTH123', $result['auth']);
    }

    public function test_parse_response_with_xml()
    {
        $service = $this->makeService();

        $xml = '<result>CAPTURED</result><auth>AUTH456</auth><ref>REF789</ref><trackid>track-2</trackid>';

        $result = $service->publicParseResponse($xml);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('AUTH456', $result['auth']);
        $this->assertEquals('REF789', $result['ref']);
        $this->assertEquals('track-2', $result['trackid']);
    }

    public function test_parse_response_throws_on_empty_output()
    {
        $service = $this->makeService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response received from KNET');

        $service->publicParseResponse('');
    }

    public function test_normalize_response_converts_empty_strings_to_null()
    {
        $service = $this->makeService();

        $result = $service->publicNormalizeResponse([
            'result' => 'CAPTURED',
            'auth' => '',
            'ref' => 'null',
        ]);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertNull($result['auth']);
        $this->assertNull($result['ref']);
    }

    public function test_normalize_response_cleans_failure_result()
    {
        $service = $this->makeService();

        $result = $service->publicNormalizeResponse([
            'result' => 'FAILURE(Transaction declined)',
        ]);

        $this->assertEquals('Transaction declined', $result['result']);
    }

    public function test_normalize_response_ensures_default_fields()
    {
        $service = $this->makeService();

        $result = $service->publicNormalizeResponse([]);

        $expectedFields = [
            'result', 'auth', 'ref', 'avr', 'postdate', 'tranid',
            'trackid', 'payid', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'amt',
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result, "Missing default field: {$field}");
            $this->assertNull($result[$field]);
        }
    }

    public function test_normalize_response_preserves_existing_fields()
    {
        $service = $this->makeService();

        $result = $service->publicNormalizeResponse([
            'result' => 'CAPTURED',
            'amt' => '10.000',
            'custom_field' => 'preserved',
        ]);

        $this->assertEquals('CAPTURED', $result['result']);
        $this->assertEquals('10.000', $result['amt']);
        $this->assertEquals('preserved', $result['custom_field']);
    }
}

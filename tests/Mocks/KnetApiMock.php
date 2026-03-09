<?php

namespace Asciisd\Knet\Tests\Mocks;

use Illuminate\Support\Facades\Http;

class KnetApiMock
{
    public static function transportId(): string
    {
        return env('KNET_TRANSPORT_ID');
    }

    public static function transportPassword(): string
    {
        return env('KNET_TRANSPORT_PASSWORD');
    }

    public static function resourceKey(): string
    {
        return env('KNET_RESOURCE_KEY');
    }

    public static function paymentUrl(): string
    {
        return env('KNET_TEST_PAYMENT_URL');
    }

    public static function inquiryUrl(): string
    {
        return env('KNET_TEST_INQUIRY_URL');
    }

    public static function fakeInquirySuccess(array $overrides = []): void
    {
        $defaults = [
            'result' => 'CAPTURED',
            'auth' => 'A12345',
            'ref' => '327200012345',
            'avr' => 'N',
            'postdate' => now()->format('mdHi'),
            'tranid' => '9876543210123',
            'trackid' => 'test-track-id',
            'payid' => '20241234567890',
            'amt' => '10.000',
            'udf1' => null,
            'udf2' => null,
            'udf3' => null,
            'udf4' => null,
            'udf5' => 'TrackID',
        ];

        $data = array_merge($defaults, $overrides);

        Http::fake([
            self::inquiryUrl().'*' => Http::response(self::buildXmlResponse($data)),
        ]);
    }

    public static function fakeInquiryNotCaptured(array $overrides = []): void
    {
        $defaults = [
            'result' => 'NOT CAPTURED',
            'auth' => null,
            'ref' => null,
            'avr' => null,
            'postdate' => null,
            'tranid' => null,
            'trackid' => 'test-track-id',
            'payid' => '20241234567890',
            'amt' => '10.000',
            'udf1' => null,
            'udf2' => null,
            'udf3' => null,
            'udf4' => null,
            'udf5' => 'TrackID',
        ];

        $data = array_merge($defaults, $overrides);

        Http::fake([
            self::inquiryUrl().'*' => Http::response(self::buildXmlResponse($data)),
        ]);
    }

    public static function fakeInquiryPending(array $overrides = []): void
    {
        $defaults = [
            'result' => 'INITIATED',
            'auth' => null,
            'ref' => null,
            'avr' => null,
            'postdate' => null,
            'tranid' => null,
            'trackid' => 'test-track-id',
            'payid' => null,
            'amt' => '10.000',
            'udf1' => null,
            'udf2' => null,
            'udf3' => null,
            'udf4' => null,
            'udf5' => 'TrackID',
        ];

        $data = array_merge($defaults, $overrides);

        Http::fake([
            self::inquiryUrl().'*' => Http::response(self::buildXmlResponse($data)),
        ]);
    }

    public static function fakeInquiryFailure(string $errorMessage = 'Transaction not found'): void
    {
        $xml = "<result>FAILURE({$errorMessage})</result>";

        Http::fake([
            self::inquiryUrl().'*' => Http::response($xml),
        ]);
    }

    public static function fakeRefundSuccess(array $overrides = []): void
    {
        $defaults = [
            'result' => 'CAPTURED',
            'auth' => 'R67890',
            'ref' => '327200098765',
            'avr' => null,
            'postdate' => now()->format('mdHi'),
            'tranid' => '1122334455667',
            'trackid' => 'test-track-id',
            'payid' => '20241234500001',
            'amt' => '10.000',
            'udf1' => null,
            'udf2' => null,
            'udf3' => null,
            'udf4' => null,
            'udf5' => 'TrackID',
        ];

        $data = array_merge($defaults, $overrides);

        Http::fake([
            self::inquiryUrl().'*' => Http::response(self::buildXmlResponse($data)),
        ]);
    }

    public static function fakeRefundFailure(string $errorMessage = 'Refund not allowed'): void
    {
        $xml = "<result>FAILURE({$errorMessage})</result>";

        Http::fake([
            self::inquiryUrl().'*' => Http::response($xml),
        ]);
    }

    public static function fakeServerError(): void
    {
        Http::fake([
            self::inquiryUrl().'*' => Http::response('Internal Server Error', 500),
        ]);
    }

    public static function fakeTimeout(): void
    {
        Http::fake([
            self::inquiryUrl().'*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);
    }

    public static function fakeEmptyResponse(): void
    {
        Http::fake([
            self::inquiryUrl().'*' => Http::response(''),
        ]);
    }

    public static function fakeJsonResponse(array $data): void
    {
        Http::fake([
            self::inquiryUrl().'*' => Http::response(json_encode($data)),
        ]);
    }

    public static function fakeSequence(array $responses): void
    {
        $sequence = Http::fakeSequence(self::inquiryUrl().'*');

        foreach ($responses as $response) {
            if (is_array($response)) {
                $sequence->push(self::buildXmlResponse($response));
            } elseif (is_string($response)) {
                $sequence->push($response);
            }
        }
    }

    private static function buildXmlResponse(array $data): string
    {
        $xml = '';
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $xml .= "<{$key}>{$value}</{$key}>";
        }
        return $xml;
    }

    public static function buildExpectedInquiryXml(string $amount, string $trackid, string $action = '8'): string
    {
        return '<request>'
            .'<id>'.self::transportId().'</id>'
            .'<password>'.self::transportPassword().'</password>'
            .'<action>'.$action.'</action>'
            .'<amt>'.$amount.'</amt>'
            .'<transid>'.$trackid.'</transid>'
            .'<udf5>TrackID</udf5>'
            .'<trackid>'.$trackid.'</trackid>'
            .'</request>';
    }
}

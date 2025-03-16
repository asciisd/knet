<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Support\Facades\Http;

abstract class AbstractKnetService
{
    protected const ACTION_PURCHASE = '1';
    protected const ACTION_REFUND = '2';
    protected const ACTION_INQUIRY = '8';

    public function __construct(
        protected readonly KnetConfig                $config,
        protected readonly KnetTransactionRepository $repository
    )
    {
    }

    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 3, '.', '');
    }

    protected function buildInquiryXml(float|string $amount, string $trackid, string $action): string
    {
        return '<request>'
            ."<id>".$this->config->getTransportId()."</id>"
            ."<password>".$this->config->getTransportPassword()."</password>"
            ."<action>".$action."</action>"
            ."<amt>".$this->formatAmount((float)$amount)."</amt>"
            ."<transid>".$trackid."</transid>"
            ."<udf5>TrackID</udf5>"
            ."<trackid>".$trackid."</trackid>"
            .'</request>';
    }

    protected function sendRequest(string $url, string $xmlData): array
    {
        try {
            $response = Http::withBody($xmlData, 'application/xml')->post($url);

            if ($response->failed()) {
                throw new \RuntimeException(
                    sprintf(
                        'Failed to get response from KNET. Status: %s, Body: %s',
                        $response->status(),
                        $response->body()
                    )
                );
            }

            return $this->parseResponse($response->body());
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to get response from KNET: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    protected function parseResponse(string $output): array
    {
        if (empty($output)) {
            throw new \RuntimeException('Empty response received from KNET');
        }

        // If it's already JSON, return it directly
        if ($decodedJson = json_decode($output, true)) {
            return $this->normalizeResponse($decodedJson);
        }

        // Try to parse as XML
        $xml = "<div>".$output."</div>";
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \RuntimeException(
                'Failed to parse KNET response: '.
                implode(', ', array_map(fn ($error) => $error->message, $errors))
            );
        }

        $result = json_decode(json_encode($xml), true);
        return $this->normalizeResponse($result ?: []);
    }

    protected function normalizeResponse(array $response): array
    {
        // Convert empty strings and "null" strings to actual null values
        $response = array_map(function ($value) {
            if ($value === '' || $value === 'null') {
                return null;
            }
            return $value;
        }, $response);

        // Clean up the result status if it contains FAILURE()
        if (isset($response['result']) && str_contains($response['result'], 'FAILURE(')) {
            $response['result'] = trim(str_replace(['FAILURE(', ')'], '', $response['result']));
        }

        // Ensure all expected fields are present
        $defaultFields = [
            'result' => null,
            'auth' => null,
            'ref' => null,
            'avr' => null,
            'postdate' => null,
            'tranid' => null,
            'trackid' => null,
            'payid' => null,
            'udf1' => null,
            'udf2' => null,
            'udf3' => null,
            'udf4' => null,
            'udf5' => null,
            'amt' => null,
        ];

        return array_merge($defaultFields, $response);
    }
}

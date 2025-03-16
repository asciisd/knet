<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Contracts\PaymentServiceInterface;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class KnetPaymentService implements PaymentServiceInterface
{
    private const ACTION_PURCHASE = '1';
    private const ACTION_INQUIRY = '8';

    private array $paymentData = [
        'id' => null,
        'password' => null,
        'action' => null,
        'langid' => null,
        'currencycode' => null,
        'responseURL' => null,
        'errorURL' => null,
        'amt' => null,
        'trackid' => null,
        'transid' => null,
        'udf1' => null,
        'udf2' => null,
        'udf3' => null,
        'udf4' => null,
        'udf5' => null,
        'user_id' => null,
        'result' => null,
        'trandata' => null,
        'tranportalId' => null,
    ];

    private array $reqParams = ['trandata', 'tranportalId', 'responseURL', 'errorURL'];
    private array $paramsToEncrypt = [
        'id', 'password', 'action', 'langid', 'currencycode', 'amt', 'responseURL', 'errorURL',
        'trackid', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5',
    ];

    public function __construct(
        private readonly KnetConfig                   $config,
        private readonly KnetTransactionRepository    $repository,
        private readonly KnetPaymentInitiationService $paymentService,
        private readonly KnetInquiryService           $inquiryService,
        private readonly KnetRefundService            $refundService
    )
    {
        $this->initializePaymentConfig();
    }

    /**
     * Create a new payment transaction
     */
    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction
    {
        return $this->paymentService->createPayment($user, $amount, $options);
    }

    /**
     * Handle the payment response
     */
    public function handlePaymentResponse(array $payload): KnetTransaction
    {
        return $this->paymentService->handlePaymentResponse($payload);
    }

    /**
     * Inquire about a payment transaction and update its status
     */
    public function inquireAndUpdateTransaction(KnetTransaction $transaction): KnetTransaction
    {
        return $this->inquiryService->inquireAndUpdateTransaction($transaction);
    }

    /**
     * Inquire about a payment transaction
     */
    public function inquirePayment(float|string $amount, string $trackid): array
    {
        return $this->inquiryService->inquirePayment($amount, $trackid);
    }

    /**
     * Process a refund for a transaction
     *
     * @param KnetTransaction $transaction The transaction to refund
     * @param float|null $amount The amount to refund. If null, refunds the full amount
     * @return array The refund response
     * @throws RequestException If the refund request fails
     */
    public function refundPayment(KnetTransaction $transaction, ?float $amount = null): array
    {
        return $this->refundService->refundPayment($transaction, $amount);
    }

    private function initializePaymentConfig(): void
    {
        $this->setPaymentData([
            'id' => $this->config->getTransportId(),
            'tranportalId' => $this->config->getTransportId(),
            'password' => $this->config->getTransportPassword(),
            'action' => self::ACTION_PURCHASE,
            'langid' => Config::get('knet.language', 'EN'),
            'currencycode' => Config::get('knet.currency', 414),
            'responseURL' => URL::to(Config::get('knet.response_url')),
            'errorURL' => URL::to(Config::get('knet.error_url')),
        ]);
    }

    private function setPaymentData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->paymentData)) {
                $this->paymentData[$key] = $value;
            }
        }
    }

    private function generatePaymentUrl(): string
    {
        $this->setPaymentData(['trandata' => $this->generateEncryptedParams()]);
        $params = $this->buildUrlParams();

        return $this->config->getPaymentUrl().'?param=paymentInit&'.$params;
    }

    private function generateEncryptedParams(): string
    {
        $params = $this->buildParamsString($this->paramsToEncrypt);
        return KPayClient::encryptAES($params, $this->config->getResourceKey());
    }

    private function buildUrlParams(): string
    {
        return $this->buildParamsString($this->reqParams);
    }

    private function buildParamsString(array $keys): string
    {
        $params = '';
        foreach ($keys as $key) {
            if (isset($this->paymentData[$key]) && $this->paymentData[$key] !== null) {
                $params = $this->appendParam($params, $key, $this->paymentData[$key]);
            }
        }
        return $params;
    }

    private function appendParam(string $current, string $key, string $value): string
    {
        return $current === '' ? "{$key}={$value}" : "{$current}&{$key}={$value}";
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 3, '.', '');
    }

    private function getInquiryUrl(): string
    {
        return $this->config->getInquiryUrl().'?param=tranInit';
    }

    private function buildInquiryXml(float|string $amount, string $trackid, string $action = self::ACTION_INQUIRY): string
    {
        return "<id>".$this->config->getTransportId()."</id>"
            ."<password>".$this->config->getTransportPassword()."</password>"
            ."<action>".$action."</action>"
            ."<amt>".$this->formatAmount((float)$amount)."</amt>"
            ."<transid>".$trackid."</transid>"
            ."<udf5>TrackID</udf5>"
            ."<trackid>".$trackid."</trackid>";
    }

    private function sendInquiryRequest(string $url, string $xmlData): array
    {
        try {
            $response = Http::withBody($xmlData, 'text/xml')->post($url);

            if ($response->failed()) {
                throw new \RuntimeException(
                    sprintf(
                        'Failed to get response from KNET. Status: %s, Body: %s',
                        $response->status(),
                        $response->body()
                    )
                );
            }

            return $this->parseInquiryResponse($response->body());
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Failed to get response from KNET: '.$e->getMessage(), $e->getCode()
            );
        }
    }

    private function parseInquiryResponse(string $output): array
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

    private function normalizeResponse(array $response): array
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

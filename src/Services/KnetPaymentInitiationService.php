<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Factories\PaymentFactory;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Database\Eloquent\Model;

class KnetPaymentInitiationService extends AbstractKnetService
{
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
        KnetConfig                              $config,
        KnetTransactionRepository               $repository,
        private readonly PaymentResponseHandler $responseHandler
    )
    {
        parent::__construct($config, $repository);
        $this->initializePaymentConfig();
    }

    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction
    {
        $request = PaymentFactory::createRequest($user, $amount, $options);

        $this->setPaymentData(array_merge([
            'amt' => $this->formatAmount($amount),
            'trackid' => $request->trackId,
            'udf1' => $request->udf1,
            'udf2' => $request->udf2,
            'udf3' => $request->udf3,
            'udf4' => $request->udf4,
            'udf5' => $request->udf5,
            'user_id' => $user->id,
            'result' => 'INITIATED',
        ], $options));

        $url = $this->generatePaymentUrl();

        return $this->repository->create([
            'user_id' => $user->id,
            'amt' => $this->paymentData['amt'],
            'livemode' => ! $this->config->isDebugMode(),
            'url' => $url,
            'trackid' => $this->paymentData['trackid'],
            'udf1' => $this->paymentData['udf1'],
            'udf2' => $this->paymentData['udf2'],
            'udf3' => $this->paymentData['udf3'],
            'udf4' => $this->paymentData['udf4'],
            'udf5' => $this->paymentData['udf5'],
            'result' => $this->paymentData['result'],
        ]);
    }

    public function handlePaymentResponse(array $payload): KnetTransaction
    {
        return $this->responseHandler->handle($payload);
    }

    private function initializePaymentConfig(): void
    {
        $this->setPaymentData([
            'id' => $this->config->getTransportId(),
            'tranportalId' => $this->config->getTransportId(),
            'password' => $this->config->getTransportPassword(),
            'action' => self::ACTION_PURCHASE,
            'langid' => config('knet.language', 'EN'),
            'currencycode' => config('knet.currency', 414),
            'responseURL' => url(config('knet.response_url')),
            'errorURL' => url(config('knet.error_url')),
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
}

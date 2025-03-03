<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Contracts\PaymentServiceInterface;
use Asciisd\Knet\DataTransferObjects\PaymentRequest;
use Asciisd\Knet\Factories\PaymentFactory;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Database\Eloquent\Model;

class KnetPaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly KnetConfig $config,
        private readonly KnetTransactionRepository $repository,
        private readonly PaymentResponseHandler $responseHandler
    ) {}

    /**
     * Create a new payment transaction
     */
    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction
    {
        $request = PaymentFactory::createRequest($user, $amount, $options);
        
        return $this->repository->create([
            'user_id' => $user->id,
            'amount' => $amount,
            'livemode' => !$this->config->isDebugMode(),
            ...$request->toArray()
        ]);
    }

    /**
     * Handle the payment response
     */
    public function handlePaymentResponse(array $payload): KnetTransaction
    {
        return $this->responseHandler->handle($payload);
    }
} 
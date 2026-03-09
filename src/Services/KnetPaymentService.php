<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Contracts\PaymentServiceInterface;
use Asciisd\Knet\KnetTransaction;
use Illuminate\Database\Eloquent\Model;

class KnetPaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly KnetPaymentInitiationService $paymentService,
        private readonly KnetInquiryService $inquiryService,
        private readonly KnetRefundService $refundService,
    ) {}

    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction
    {
        return $this->paymentService->createPayment($user, $amount, $options);
    }

    public function handlePaymentResponse(array $payload): KnetTransaction
    {
        return $this->paymentService->handlePaymentResponse($payload);
    }

    public function inquireAndUpdateTransaction(KnetTransaction $transaction): KnetTransaction
    {
        return $this->inquiryService->inquireAndUpdateTransaction($transaction);
    }

    public function inquirePayment(float|string $amount, string $trackid): array
    {
        return $this->inquiryService->inquirePayment($amount, $trackid);
    }

    public function refundPayment(KnetTransaction $transaction, ?float $amount = null): array
    {
        return $this->refundService->refundPayment($transaction, $amount);
    }
}

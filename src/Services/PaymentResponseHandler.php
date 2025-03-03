<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\DataTransferObjects\KnetResponse;
use Asciisd\Knet\Enums\PaymentStatus;
use Asciisd\Knet\Events\KnetPaymentFailed;
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Repositories\KnetTransactionRepository;

class PaymentResponseHandler
{
    public function __construct(
        private readonly KnetTransactionRepository $repository
    ) {}

    public function handle(array $payload): KnetTransaction
    {
        $response = KnetResponse::fromArray($payload);
        $transaction = $this->repository->findByTrackId($response->trackId);

        $this->updateTransaction($transaction, $response);
        $this->dispatchEvents($transaction, $response);

        return $transaction;
    }

    private function updateTransaction(KnetTransaction $transaction, KnetResponse $response): void
    {
        $status = $this->determineStatus($response->result);

        $this->repository->update($transaction, [
            'paymentid' => $response->paymentId,
            'result' => $status->value,
            'paid' => $status->isPaid(),
            'auth' => $response->auth,
            'ref' => $response->reference,
            'tranid' => $response->transactionId,
            'postdate' => $response->postDate,
            'error_text' => $response->errorText,
        ]);
    }

    private function determineStatus(string $result): PaymentStatus
    {
        return match($result) {
            'CAPTURED' => PaymentStatus::CAPTURED,
            'FAILED' => PaymentStatus::FAILED,
            default => PaymentStatus::PENDING,
        };
    }

    private function dispatchEvents(KnetTransaction $transaction, KnetResponse $response): void
    {
        if ($response->paid) {
            KnetPaymentSucceeded::dispatch($transaction);
        } else {
            KnetPaymentFailed::dispatch($transaction);
        }
    }
}

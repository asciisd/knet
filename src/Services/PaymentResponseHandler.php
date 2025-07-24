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
    )
    {
    }

    public function handle(array $payload): KnetTransaction
    {
        $response = KnetResponse::fromArray($payload);
        $transaction = $this->repository->findByTrackId($response->trackId);

        $this->updateTransaction($transaction, $response);
        $this->dispatchEvents($transaction, $response);

        return $transaction;
    }

    public function handleError(array $payload)
    {
        // TODO: Implement handleError() method.
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
            'amt' => $response->amount,
            'avr' => $response->avr,
            'udf1' => $response->udf1,
            'udf2' => $response->udf2,
            'udf3' => $response->udf3,
            'udf4' => $response->udf4,
            'udf5' => $response->udf5,
            'udf6' => $response->udf6,
            'udf7' => $response->udf7,
            'udf8' => $response->udf8,
            'udf9' => $response->udf9,
            'udf10' => $response->udf10,
        ]);
    }

    private function determineStatus(string $result): PaymentStatus
    {
        return match (strtoupper($result)) {
            'CAPTURED' => PaymentStatus::CAPTURED,
            'FAILED' => PaymentStatus::FAILED,
            'NOT CAPTURED' => PaymentStatus::NOT_CAPTURED,
            'ABANDONED' => PaymentStatus::ABANDONED,
            'CANCELLED' => PaymentStatus::CANCELLED,
            'DECLINED' => PaymentStatus::DECLINED,
            'RESTRICTED' => PaymentStatus::RESTRICTED,
            'VOID' => PaymentStatus::VOID,
            'TIMEDOUT' => PaymentStatus::TIMEDOUT,
            'UNKNOWN' => PaymentStatus::UNKNOWN,
            'INITIATED' => PaymentStatus::INITIATED,
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

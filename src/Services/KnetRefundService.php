<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Contracts\RefundsPayments;
use Asciisd\Knet\Events\KnetRefundFailed;
use Asciisd\Knet\Events\KnetRefundSucceeded;
use Asciisd\Knet\Exceptions\KnetException;
use Asciisd\Knet\KnetTransaction;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class KnetRefundService extends AbstractKnetService implements RefundsPayments
{
    /**
     * @throws KnetException If the transaction is not refundable or the amount is invalid
     * @throws RequestException If the refund request fails
     */
    public function refundPayment(KnetTransaction $transaction, ?float $amount = null): array
    {
        $this->validateRefund($transaction, $amount);

        $refundAmount = $amount ?? $transaction->rawAmount();

        // Create a new transaction for the refund
        $refundTransaction = $this->createRefundTransaction($transaction, $refundAmount);

        $xmlData = $this->buildInquiryXml($refundAmount, $transaction->trackid, self::ACTION_REFUND);
        $url = $this->config->getInquiryUrl().'?param=tranInit';

        try {
            $response = $this->sendRequest($url, $xmlData);

            if (isset($response['result']) && $response['result'] === 'CAPTURED') {
                // Update both transactions
                $this->repository->update($transaction, [
                    'refunded' => true,
                    'refunded_at' => Carbon::now(),
                    'refund_amount' => $this->formatAmount($refundAmount),
                ]);

                $this->repository->update($refundTransaction, [
                    'result' => $response['result'],
                    'auth' => $response['auth'],
                    'ref' => $response['ref'],
                    'postdate' => $response['postdate'],
                    'tranid' => $response['tranid'],
                    'paymentid' => $response['payid'],
                    'paid' => true,
                ]);

                KnetRefundSucceeded::dispatch($transaction, $refundTransaction, $refundAmount);
            } else {
                $reason = $response['result'] ?? 'Unknown error';

                $this->repository->update($refundTransaction, [
                    'result' => $response['result'] ?? 'FAILED',
                    'error_text' => $response['error_message'] ?? $reason,
                ]);

                KnetRefundFailed::dispatch($transaction, $refundTransaction, $reason);
            }

            return $response;
        } catch (\Exception $e) {
            // Update refund transaction status to failed
            $this->repository->update($refundTransaction, [
                'result' => 'FAILED',
                'error_text' => $e->getMessage(),
            ]);

            KnetRefundFailed::dispatch($transaction, $refundTransaction, $e->getMessage());

            Log::error('Knet Refund Error:', [
                'message' => $e->getMessage(),
                'transaction_id' => $transaction->id,
                'refund_transaction_id' => $refundTransaction->id,
                'track_id' => $transaction->trackid,
                'amount' => $refundAmount,
                'url' => $url,
                'xml_data' => $xmlData,
                'response' => $e instanceof RequestException ? $e->response?->body() : null,
            ]);

            throw $e;
        }
    }

    private function validateRefund(KnetTransaction $transaction, ?float $amount): void
    {
        if (! $transaction->isRefundable()) {
            throw new KnetException('Transaction is not refundable. It must be captured and not already refunded.');
        }

        if ($amount !== null) {
            if ($amount <= 0) {
                throw new KnetException('Refund amount must be greater than zero.');
            }

            if ($amount > $transaction->rawAmount()) {
                throw new KnetException(
                    sprintf('Refund amount (%.3f) exceeds the original transaction amount (%.3f).', $amount, $transaction->rawAmount())
                );
            }
        }
    }

    private function createRefundTransaction(KnetTransaction $originalTransaction, float $refundAmount): KnetTransaction
    {
        return $this->repository->create([
            'user_id' => $originalTransaction->user_id,
            'amt' => $this->formatAmount($refundAmount),
            'livemode' => $originalTransaction->livemode,
            'trackid' => $originalTransaction->trackid,
            'original_transaction_id' => $originalTransaction->id,
            'action' => 2,
            'result' => 'INITIATED',
            'udf1' => $originalTransaction->udf1,
            'udf2' => $originalTransaction->udf2,
            'udf3' => $originalTransaction->udf3,
            'udf4' => $originalTransaction->udf4,
            'udf5' => 'TrackID',
        ]);
    }
}

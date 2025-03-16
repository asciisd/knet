<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class KnetRefundService extends AbstractKnetService
{
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
            }

            return $response;
        } catch (\Exception $e) {
            // Update refund transaction status to failed
            $this->repository->update($refundTransaction, [
                'result' => 'FAILED',
                'error_text' => $e->getMessage(),
            ]);

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

    /**
     * Create a new transaction record for a refund
     */
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

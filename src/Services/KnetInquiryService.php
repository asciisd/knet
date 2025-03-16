<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\KnetTransaction;
use Illuminate\Support\Facades\Event;

class KnetInquiryService extends AbstractKnetService
{
    /**
     * Inquire about a payment transaction and update its status
     */
    public function inquireAndUpdateTransaction(KnetTransaction $transaction): KnetTransaction
    {
        $inquiryResult = $this->inquirePayment($transaction->rawAmount(), $transaction->trackid);

        $updateData = [
            'result' => $inquiryResult['result'] ?? $transaction->result,
            'auth' => $inquiryResult['auth'] ?? $transaction->auth,
            'ref' => $inquiryResult['ref'] ?? $transaction->ref,
            'avr' => $inquiryResult['avr'] ?? $transaction->avr,
            'postdate' => $inquiryResult['postdate'] ?? $transaction->postdate,
            'tranid' => $inquiryResult['tranid'] ?? $transaction->tranid,
            'trackid' => $inquiryResult['trackid'] ?? $transaction->trackid,
            'paymentid' => $inquiryResult['payid'] ?? $transaction->paymentid,
            'amount' => $inquiryResult['amt'] ?? $transaction->amount,
            'paid' => $inquiryResult['result'] === 'SUCCESS' ? true : false,
            'udf1' => $inquiryResult['udf1'] ?? $transaction->udf1,
            'udf2' => $inquiryResult['udf2'] ?? $transaction->udf2,
            'udf3' => $inquiryResult['udf3'] ?? $transaction->udf3,
            'udf4' => $inquiryResult['udf4'] ?? $transaction->udf4,
            'udf5' => $inquiryResult['udf5'] ?? $transaction->udf5,
        ];

        $transaction = $this->repository->update($transaction, $updateData);

        if ($transaction->wasChanged()) {
            Event::dispatch(new KnetTransactionUpdated($transaction));
        }

        return $transaction;
    }

    /**
     * Inquire about a payment transaction
     */
    public function inquirePayment(float|string $amount, string $trackid): array
    {
        $url = $this->config->getInquiryUrl().'?param=tranInit';
        $xmlData = $this->buildInquiryXml($amount, $trackid, self::ACTION_INQUIRY);

        return $this->sendRequest($url, $xmlData);
    }
}

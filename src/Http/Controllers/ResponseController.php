<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Events\KnetTransactionHasErrors;
use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\KPayResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ResponseController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = KPayClient::decryptAES($request->getContent(), config('knet.resource_key'));

        parse_str($payload, $payloadArray);

        KnetResponseReceived::dispatch($payloadArray);

        $knetResponseHandler = KPayResponseHandler::make($payloadArray, $request);

        // find transaction by track id
        $transaction = KnetTransaction::findByTrackId($payloadArray['trackid']);
        $transaction->forceFill($knetResponseHandler->toArray())->save();
        KnetTransactionUpdated::dispatch($transaction);

        // check if current response is duplicated and this transaction is already captured,
        // so don't update the transaction
        if ($knetResponseHandler->isDuplicated() && $transaction->isCaptured()) {
            KnetTransactionHasErrors::dispatch($transaction);
        }

        // check if current response is invalid payment status and this transaction is already captured,
        // so don't update the transaction status
        if ($knetResponseHandler->isInvalidPaymentStatus() && $transaction->isCaptured()) {
            KnetTransactionHasErrors::dispatch($transaction);
        }

        KnetResponseHandled::dispatch($knetResponseHandler->toArray());

        return 'REDIRECT='.route('knet.handle');
    }
}

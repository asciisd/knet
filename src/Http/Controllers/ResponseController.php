<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Events\KnetTransactionHasErrors;
use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayResponseHandler;
use Asciisd\Knet\Services\KnetResponseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ResponseController extends Controller
{
    public function __invoke(Request $request)
    {
        // Decrypt and parse response
        $payloadArray = KnetResponseService::decryptAndParse($request);

        logger()->info('ResponseController | Knet Response: ', $payloadArray);
        logger()->info('ResponseController | Knet Header: ', $request->header());

        // Dispatch received event
        KnetResponseReceived::dispatch($payloadArray);

        // Handle Knet response
        $knetResponseHandler = KPayResponseHandler::make($payloadArray, $request);

        // Find transaction by track ID
        $transaction = KnetTransaction::findByTrackId($payloadArray['trackid']);

        // Update transaction
        $transaction->forceFill($knetResponseHandler->toArray())->save();
        KnetTransactionUpdated::dispatch($transaction);

        // Handle duplicated response or invalid status
        if (($knetResponseHandler->isDuplicated() || $knetResponseHandler->isInvalidPaymentStatus())
            && $transaction->isCaptured()) {
            KnetTransactionHasErrors::dispatch($transaction);
        }

        // Dispatch response handled event
        KnetResponseHandled::dispatch($knetResponseHandler->toArray());

        echo 'REDIRECT='.route('knet.handle');
    }
}

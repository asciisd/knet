<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Events\KnetTransactionHasErrors;
use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\KPayResponseHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class KnetController extends Controller
{
    public function response(Request $request)
    {
        $payload = KPayClient::decryptAES($request->getContent(), config('knet.resource_key'));

        parse_str($payload, $payloadArray);

        KnetResponseReceived::dispatch($payloadArray);

        $knetResponseHandler = KPayResponseHandler::make($payloadArray, $request);

        // find transaction by track id
        $transaction = KnetTransaction::findByTrackId($payloadArray['trackid']);

        return 'REDIRECT='.route('knet.handle');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response|Redirector
     */
    public function handle(Request $request)
    {
        // check if current response is duplicated and this transaction is already captured,
        // so don't update the transaction
//        if ($knetResponseHandler->isDuplicated() && $transaction->isCaptured()) {
//            $transaction->update($knetResponseHandler->errorsToArray());
//            KnetTransactionHasErrors::dispatch($transaction);
//            return $this->error($request);
//        }

        // check if current response is invalid payment status and this transaction is already captured,
        // so don't update the transaction status
//        if ($knetResponseHandler->isInvalidPaymentStatus() && $transaction->isCaptured()) {
//            $transaction->update($knetResponseHandler->errorsToArray());
//            KnetTransactionHasErrors::dispatch($transaction);
//
//            return $this->error($request);
//        }

//        $transaction->update($knetResponseHandler->toArray());
//        KnetTransactionUpdated::dispatch($transaction);
//        KnetResponseHandled::dispatch($knetResponseHandler->toArray());

        //return success
//        return $this->successMethod();
    }

    public function error(Request $request)
    {
        logger()->error('Knet error occurred with response data: ', $request->all());

        return redirect(
            url(config('knet.error_url'))
        );
    }

    /**
     * Handle successful calls on the controller.
     *
     * @return RedirectResponse|Redirector|Response
     */
    protected function successMethod()
    {
        logger()->info('Knet transaction handled successfully.');

        return redirect(
            url(config('knet.redirect_url'))
        );
    }
}

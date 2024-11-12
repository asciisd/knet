<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Events\KnetTransactionHasErrors;
use Asciisd\Knet\Events\KnetTransactionUpdated;
use Asciisd\Knet\Http\Middleware\VerifyKnetResponseSignature;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayResponseHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class KnetController extends Controller
{
    /**
     * Create a new KnetController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(VerifyKnetResponseSignature::class);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse|Response|Redirector
     */
    public function handleKnet(Request $request)
    {
        KnetResponseReceived::dispatch($request->all());

        $knetResponseHandler = new KPayResponseHandler();

        // find transaction by track id
        $transaction = KnetTransaction::findByTrackId($request->input('trackid'));

        // check if current response is duplicated and this transaction is already captured,
        // so don't update the transaction
        if ($knetResponseHandler->isDuplicated() && $transaction->isCaptured()) {
            $transaction->update($knetResponseHandler->errorsToArray());
            KnetTransactionHasErrors::dispatch($transaction);
            return $this->error($request);
        }

        // check if current response is invalid payment status and this transaction is already captured,
        // so don't update the transaction status
        if ($knetResponseHandler->isInvalidPaymentStatus() && $transaction->isCaptured()) {
            $transaction->update($knetResponseHandler->errorsToArray());
            KnetTransactionHasErrors::dispatch($transaction);
            return $this->error($request);
        }

        $transaction->update($knetResponseHandler->toArray());
        KnetTransactionUpdated::dispatch($transaction);
        KnetResponseHandled::dispatch($knetResponseHandler->toArray());

        //return success
        return $this->successMethod();
    }

    public function error(Request $request)
    {
        logger()->error('Knet error occurred with response data: ', $request->all());

        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     *
     * @return RedirectResponse|Redirector|Response
     */
    protected function successMethod()
    {
        return redirect(
            url(config('knet.redirect_url'))
        );
    }
}

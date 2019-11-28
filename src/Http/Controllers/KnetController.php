<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Http\Middleware\VerifyKnetResponseSignature;
use Asciisd\Knet\KnetResponseHandler;
use Asciisd\Knet\KnetTransaction;
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

    public function handleKnet(Request $request)
    {
        KnetResponseReceived::dispatch($request->all());

        $knetResponseHandler = new KnetResponseHandler();

        // update transaction
        KnetTransaction::findByTrackId($request->input('trackid'))
            ->update($knetResponseHandler->toArray());

        //todo: notify user
        //todo: send emails

        KnetResponseHandled::dispatch($knetResponseHandler->toArray());

        //return success
        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     *
     * @param array $parameters
     * @return RedirectResponse|Redirector|Response
     */
    protected function successMethod($parameters = [])
    {
        return redirect(url(config('knet.success_url')));
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param array $parameters
     * @return Response
     */
    protected function missingMethod($parameters = [])
    {
        return new Response;
    }
}

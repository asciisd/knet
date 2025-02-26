<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Services\KnetResponseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        // Decrypt and parse response
        $payloadArray = KnetResponseService::decryptAndParse($request);

        logger()->info('ResponseController | Knet Response: ', $payloadArray);
        logger()->info('ResponseController | Knet Header: ', $request->header());

        // Dispatch received event
        KnetResponseReceived::dispatch($payloadArray);

        echo 'REDIRECT='.route('knet.handle');
    }
}

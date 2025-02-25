<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\KPayClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = KPayClient::decryptAES($request->getContent(), config('knet.resource_key'));
        parse_str($payload, $payloadArray);

        logger()->info('HandleController | Knet Response: ', $payloadArray);

        KnetResponseReceived::dispatch($payloadArray);

        $baseUrl = config('knet.redirect_url');

        echo "REDIRECT=" . redirect($baseUrl)->getTargetUrl() . '&paymentId=' . $payloadArray['paymentId'];
    }
}

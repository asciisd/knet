<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseReceived;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        logger()->info('HandleController | Knet Response: ', $request->all());

        KnetResponseReceived::dispatch($request->all());

        $baseUrl = config('knet.redirect_url');

        echo "REDIRECT=" . redirect($baseUrl)->getTargetUrl();
    }
}

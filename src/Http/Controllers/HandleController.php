<?php

namespace Asciisd\Knet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        logger()->info($request->getMethod().' | HandleController | Request Header: ', $request->header());
        logger()->info($request->getMethod().' | HandleController | Request Content: '.$request->getContent());

        return redirect(config('knet.redirect_url'));
    }
}

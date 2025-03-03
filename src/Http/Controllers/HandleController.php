<?php

namespace Asciisd\Knet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        logger()->info($request->getMethod() .' | HandleController | Knet Header: ', $request->header());
        logger()->info($request->getContent());

        return redirect(config('knet.redirect_url'));
    }
}

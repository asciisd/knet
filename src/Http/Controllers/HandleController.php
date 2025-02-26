<?php

namespace Asciisd\Knet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        logger()->info($request->getContent());
        logger()->info('ResponseController | Knet Header: ', $request->header());

        return redirect(config('knet.redirect_url'));
    }
}

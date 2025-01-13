<?php

namespace Asciisd\Knet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ErrorController extends Controller
{
    public function __invoke(Request $request)
    {
        $params = [
            'paymentid' => $request->paymentid,
            'result' => $request->result,
        ];

        return redirect(config('knet.redirect_url').'?'.http_build_query($params));
    }
}

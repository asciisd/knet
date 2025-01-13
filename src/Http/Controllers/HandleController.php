<?php

namespace Asciisd\Knet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandleController extends Controller
{
    public function __invoke(Request $request)
    {
        $baseUrl = config('knet.redirect_url');
        $params = [
            'paymentid' => $request->paymentid,
            'result' => $request->result,
        ];

        return redirect($baseUrl.'?'.http_build_query($params));
    }
}

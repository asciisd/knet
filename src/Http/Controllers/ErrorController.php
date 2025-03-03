<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetPaymentFailed;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ErrorController extends Controller
{
    public function __invoke(Request $request)
    {
        $errorData = [
            'paymentid' => $request->paymentid,
            'result' => $request->result,
            'error' => $request->error,
            'error_text' => $request->error_text,
        ];

        // Log the error
        logger()->error('Knet Payment Error:', $errorData);
        logger()->error('ErrorController | Request', $request->all());

        // Dispatch payment failed event
//        KnetPaymentFailed::dispatch($errorData);

        // Redirect with error data
        return redirect(config('knet.redirect_url'))
            ->withErrors([
                'payment_error' => $request->error_text ?? 'Payment processing failed',
            ])
            ->with('knet_error', $errorData);
    }
}

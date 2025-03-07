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
        logger()->error($request->getMethod().' | ErrorController | Knet Payment Error:', $errorData);
        logger()->error($request->getMethod().' | ErrorController | Request All: ', $request->all());

        // Dispatch payment failed event
        KnetPaymentFailed::dispatch($request, "Payment processing failed");

        // Redirect with error data
        return redirect(config('knet.redirect_url'))
            ->with('knet_error', $errorData)
            ->withErrors([
                'payment_error' => $request->error_text ?? 'Payment processing failed',
            ]);
    }
}

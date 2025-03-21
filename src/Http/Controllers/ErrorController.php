<?php

namespace Asciisd\Knet\Http\Controllers;

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

        //ResErrorText
        if ($request->has('ErrorText')) {
            $errorData['error_text'] = $request->ErrorText;
        }

        //ResErrorNo
        if ($request->has('Error')) {
            $errorData['error'] = $request->Error;
        }

        // Log the error
        logger()->error($request->getMethod().' | ErrorController | Knet Payment Error | Error Data:', $errorData);
        logger()->error($request->getMethod().' | ErrorController | Knet Payment Error | Request All: ', $request->all());

        // Redirect with error data
        return redirect(config('knet.redirect_url'))
            ->with('knet_error', $errorData)
            ->withErrors([
                'payment_error' => $request->error_text ?? 'Payment processing failed',
            ]);
    }
}

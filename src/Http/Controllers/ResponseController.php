<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Services\KnetResponseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ResponseController extends Controller
{
    public function __construct(
        private readonly KnetPaymentService  $paymentService,
        private readonly KnetResponseService $responseService
    )
    {
    }

    public function __invoke(Request $request)
    {
        try {
            // Log incoming request
            logger()->info($request->getMethod().' | ResponseController | Knet Response:', [
                'headers' => $request->header(),
                'content' => $request->getContent()
            ]);

            // Decrypt and parse response
            $payload = $this->responseService->decryptAndParse($request);

            // Dispatch received event
            KnetResponseReceived::dispatch($payload);

            logger()->info($request->getMethod().' | ResponseController | Dispatch KnetResponseReceived', [
                'payload' => $payload
            ]);

            // Process payment
            $transaction = $this->paymentService->handlePaymentResponse($payload);

            // Dispatch handled event
            KnetResponseHandled::dispatch($payload);

            logger()->info($request->getMethod().' | ResponseController | Dispatch KnetResponseHandled', [
                'transaction' => $transaction
            ]);

            $response = 'REDIRECT='.route('knet.handle');

        } catch (\Exception $e) {
            logger()->error($request->getMethod().' | ResponseController | Knet Response Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $response = 'REDIRECT='.route('knet.error')."?error=Payment processing failed&error_text=".$e->getMessage();
        }

        // Redirect to handler
        return $response;
    }
}

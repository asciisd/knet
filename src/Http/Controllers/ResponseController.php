<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Exceptions\InvalidHexDataException;
use Asciisd\Knet\Services\KnetPaymentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResponseController extends Controller
{
    public function __construct(
        private readonly KnetPaymentService $paymentService,
    ) {}

    public function __invoke(Request $request)
    {
        try {
            $payload = $request->attributes->get('knet_payload');

            KnetResponseReceived::dispatch($payload);

            $transaction = $this->paymentService->handlePaymentResponse($payload);

            KnetResponseHandled::dispatch($payload);

            $response = 'REDIRECT='.route('knet.handle');

        } catch (InvalidHexDataException $e) {
            logger()->error('ResponseController | Invalid Hex Data Error:', [
                'error_message' => $e->getMessage(),
                'error_details' => $e->getErrorDetails(),
            ]);

            $errorDetails = 'The payment gateway sent malformed data. Please try again or contact support if the issue persists.';
            $response = 'REDIRECT='.route('knet.error').'?error=hex_validation_failed&error_text='.urlencode($errorDetails);

        } catch (AccessDeniedHttpException $e) {
            logger()->error('ResponseController | Access Denied Error:', [
                'error_message' => $e->getMessage(),
            ]);

            $errorDetails = match (true) {
                str_contains($e->getMessage(), 'Invalid response data from KNet gateway') => 'The payment gateway response could not be processed. Please try your payment again.',
                str_contains($e->getMessage(), 'No transaction data received') => 'No response received from payment gateway. Please verify your payment status.',
                str_contains($e->getMessage(), 'Missing track ID') => 'Invalid payment response format. Please contact support with your transaction details.',
                default => 'Payment verification failed. Please try again or contact support.',
            };

            $response = 'REDIRECT='.route('knet.error').'?error=verification_failed&error_text='.urlencode($errorDetails);

        } catch (\Exception $e) {
            logger()->error('ResponseController | Unexpected Error:', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
            ]);

            $errorDetails = 'An unexpected error occurred while processing your payment. Please try again or contact support.';
            $response = 'REDIRECT='.route('knet.error').'?error=processing_failed&error_text='.urlencode($errorDetails);
        }

        return $response;
    }
}

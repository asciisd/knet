<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetResponseHandled;
use Asciisd\Knet\Events\KnetResponseReceived;
use Asciisd\Knet\Exceptions\InvalidHexDataException;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Services\KnetResponseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResponseController extends Controller
{
    public function __construct(public KnetResponseService $responseService, public KnetPaymentService $paymentService)
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

        } catch (InvalidHexDataException $e) {
            // Handle hex validation errors specifically
            logger()->error($request->getMethod().' | ResponseController | Invalid Hex Data Error:', [
                'error_type' => 'invalid_hex_data',
                'error_message' => $e->getMessage(),
                'error_details' => $e->getErrorDetails(),
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->headers->get('user-agent'),
                    'content_length' => strlen($request->getContent()),
                ],
                'hex_data_info' => [
                    'has_hex_data' => !empty($e->getHexData()),
                    'hex_length' => $e->getHexData() ? strlen($e->getHexData()) : 0,
                ],
                'debug_info' => $e->getDebugInfo(),
            ]);

            // Create user-friendly error message
            $errorMessage = 'Payment processing failed due to corrupted response data from KNet gateway';
            $errorDetails = 'The payment gateway sent malformed data. Please try again or contact support if the issue persists.';

            $response = 'REDIRECT='.route('knet.error')."?error=hex_validation_failed&error_text=".urlencode($errorDetails);

        } catch (AccessDeniedHttpException $e) {
            // Handle access denied errors (invalid requests, missing data, etc.)
            logger()->error($request->getMethod().' | ResponseController | Access Denied Error:', [
                'error_type' => 'access_denied',
                'error_message' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->headers->get('user-agent'),
                    'has_content' => !empty($request->getContent()),
                    'content_length' => strlen($request->getContent()),
                ],
                'previous_error' => $e->getPrevious() ? [
                    'type' => get_class($e->getPrevious()),
                    'message' => $e->getPrevious()->getMessage(),
                ] : null,
            ]);

            // Determine specific error message based on the exception
            $errorMessage = 'Payment verification failed';
            if (str_contains($e->getMessage(), 'Invalid response data from KNet gateway')) {
                $errorDetails = 'The payment gateway response could not be processed. Please try your payment again.';
            } elseif (str_contains($e->getMessage(), 'No transaction data received')) {
                $errorDetails = 'No response received from payment gateway. Please verify your payment status.';
            } elseif (str_contains($e->getMessage(), 'Missing track ID')) {
                $errorDetails = 'Invalid payment response format. Please contact support with your transaction details.';
            } else {
                $errorDetails = 'Payment verification failed. Please try again or contact support.';
            }

            $response = 'REDIRECT='.route('knet.error')."?error=verification_failed&error_text=".urlencode($errorDetails);

        } catch (\Exception $e) {
            // Handle any other unexpected errors
            logger()->error($request->getMethod().' | ResponseController | Unexpected Error:', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_trace' => $e->getTraceAsString(),
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->headers->get('user-agent'),
                    'content_length' => strlen($request->getContent()),
                    'headers' => $request->headers->all(),
                ],
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'timestamp' => now()->toISOString(),
                ]
            ]);

            // Generic error message for unexpected errors
            $errorMessage = 'Payment processing failed';
            $errorDetails = 'An unexpected error occurred while processing your payment. Please try again or contact support.';

            $response = 'REDIRECT='.route('knet.error')."?error=processing_failed&error_text=".urlencode($errorDetails);
        }

        // Log the final response being sent
        logger()->info($request->getMethod().' | ResponseController | Sending Response:', [
            'response' => $response,
            'contains_error' => str_contains($response, 'error='),
        ]);

        return $response;
    }
}

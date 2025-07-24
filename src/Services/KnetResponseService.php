<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Exceptions\InvalidHexDataException;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class KnetResponseService
{
    public function __construct(
        private readonly KnetTransactionRepository $repository
    )
    {
    }

    /**
     * Handle the Knet payment response
     */
    public function handleResponse(Request $request): KnetTransaction
    {
        $payload = $this->decryptAndParse($request);
        return $this->repository->findByTrackId($payload['trackid']);
    }

    /**
     * Update the transaction with the response data
     */
    public function updateTransaction(KnetTransaction $transaction): KnetTransaction
    {
        return $this->repository->update($transaction, [
            'result' => $transaction->result,
            'paymentid' => $transaction->paymentid,
            'trackid' => $transaction->trackid,
            'ref' => $transaction->ref,
            'auth' => $transaction->auth,
            'tranid' => $transaction->tranid,
            'amount' => $transaction->amount,
        ]);
    }

    /**
     * Decrypts and parses Knet response payload with enhanced error handling.
     *
     * @throws AccessDeniedHttpException
     * @throws InvalidHexDataException
     */
    public static function decryptAndParse(Request $request): array
    {
        // Extract only the trandata field from the request
        $trandata = $request->input('trandata') ?? $request->get('trandata');
        
        // If trandata is not in the request parameters, try to get it from raw content
        // This handles cases where the request is sent as raw POST data
        if (empty($trandata)) {
            $rawContent = $request->getContent();
            
            // Try to parse as query string format: trandata=value&other=value
            if (!empty($rawContent) && str_contains($rawContent, 'trandata=')) {
                parse_str($rawContent, $parsedData);
                $trandata = $parsedData['trandata'] ?? null;
            }
            
            // If still empty, check if the raw content itself is the trandata
            // (some implementations might send just the encrypted data)
            if (empty($trandata) && !empty($rawContent) && ctype_xdigit(trim($rawContent))) {
                $trandata = trim($rawContent);
            }
        }

        // Enhanced validation for transaction data
        if (empty($trandata) || $trandata === null) {
            logger()->error('KnetResponseService | Empty or null transaction data received', [
                'request_method' => $request->getMethod(),
                'request_headers' => $request->headers->all(),
                'content_length' => $request->headers->get('content-length', 0),
                'user_agent' => $request->headers->get('user-agent'),
                'ip_address' => $request->ip(),
                'request_params' => $request->all(),
                'raw_content_preview' => substr($request->getContent(), 0, 100),
                'has_trandata_param' => $request->has('trandata'),
            ]);
            
            throw new AccessDeniedHttpException('Invalid Request: No trandata field found in KNet response');
        }

        // Log the extracted trandata for debugging (if enabled)
        if (config('knet.debug_response_data', false)) {
            logger()->debug('KnetResponseService | Extracted trandata from request:', [
                'trandata_length' => strlen($trandata),
                'trandata_preview' => substr($trandata, 0, 100) . '...',
                'extraction_method' => $request->has('trandata') ? 'request_parameter' : 'parsed_from_content',
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->headers->get('user-agent'),
                ]
            ]);
        }

        try {
            // Attempt to decrypt the transaction data
            $payload = KPayClient::decryptAES($trandata, config('knet.resource_key'));

            if (empty($payload)) {
                logger()->error('KnetResponseService | Decryption resulted in empty payload', [
                    'trandata_length' => strlen($trandata),
                    'trandata_preview' => substr($trandata, 0, 50) . '...',
                    'resource_key_configured' => !empty(config('knet.resource_key')),
                ]);
                
                throw new AccessDeniedHttpException('Failed to decrypt KNet response: Empty payload after decryption');
            }

            // Parse the decrypted payload
            parse_str($payload, $payloadArray);

            if (empty($payloadArray)) {
                logger()->error('KnetResponseService | Failed to parse decrypted payload', [
                    'payload_length' => strlen($payload),
                    'payload_preview' => substr($payload, 0, 200),
                ]);
                
                throw new AccessDeniedHttpException('Failed to parse KNet response: Invalid payload format');
            }

            // Validate required fields
            if (!isset($payloadArray['trackid']) || empty($payloadArray['trackid'])) {
                logger()->error('KnetResponseService | Missing or empty track ID in response', [
                    'payload_keys' => array_keys($payloadArray),
                    'payload_data' => config('knet.debug_response_data', false) ? $payloadArray : '[hidden]',
                ]);
                
                throw new AccessDeniedHttpException('Missing track ID in response: Invalid KNet response format');
            }

            // Log successful decryption and parsing
            logger()->info('KnetResponseService | Successfully decrypted and parsed KNet response', [
                'track_id' => $payloadArray['trackid'],
                'result' => $payloadArray['result'] ?? 'not_set',
                'payment_id' => $payloadArray['paymentid'] ?? 'not_set',
                'payload_fields' => count($payloadArray),
            ]);

            return $payloadArray;

        } catch (InvalidHexDataException $e) {
            // Handle hex validation errors specifically
            logger()->error('KnetResponseService | Invalid hex data in KNet response', [
                'error_type' => 'invalid_hex_data',
                'error_message' => $e->getMessage(),
                'error_details' => $e->getErrorDetails(),
                'hex_data_info' => [
                    'length' => $e->getHexData() ? strlen($e->getHexData()) : 0,
                    'preview' => $e->getHexData() ? substr($e->getHexData(), 0, 50) . '...' : 'null',
                ],
                'debug_info' => $e->getDebugInfo(),
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->headers->get('user-agent'),
                ],
                'trandata_extraction' => [
                    'from_parameter' => $request->has('trandata'),
                    'trandata_length' => strlen($trandata),
                ]
            ]);

            // If debugging is enabled, also log the hex analysis
            if (config('knet.debug_hex_conversion', false) && $e->getHexData()) {
                try {
                    $debugAnalysis = KPayClient::debugHexData($e->getHexData());
                    logger()->debug('KnetResponseService | Hex data analysis:', $debugAnalysis);
                } catch (\Exception $debugException) {
                    logger()->warning('KnetResponseService | Failed to generate hex debug analysis', [
                        'debug_error' => $debugException->getMessage()
                    ]);
                }
            }

            // Re-throw as AccessDeniedHttpException with user-friendly message
            throw new AccessDeniedHttpException(
                'Invalid response data from KNet gateway: ' . $e->getMessage(),
                $e
            );

        } catch (\Exception $e) {
            // Handle any other decryption or parsing errors
            logger()->error('KnetResponseService | Unexpected error during response processing', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'trandata_info' => [
                    'length' => strlen($trandata),
                    'preview' => substr($trandata, 0, 100) . '...',
                    'is_empty' => empty($trandata),
                ],
                'config_info' => [
                    'resource_key_set' => !empty(config('knet.resource_key')),
                    'debug_mode' => config('knet.debug', false),
                ],
                'request_info' => [
                    'method' => $request->getMethod(),
                    'ip' => $request->ip(),
                    'content_type' => $request->headers->get('content-type'),
                    'has_trandata_param' => $request->has('trandata'),
                ]
            ]);

            throw new AccessDeniedHttpException(
                'Failed to process KNet response: ' . $e->getMessage(),
                $e
            );
        }
    }
}

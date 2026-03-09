<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Contracts\EncryptsPayload;
use Asciisd\Knet\Exceptions\InvalidHexDataException;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class KnetResponseService
{
    public function __construct(
        private readonly KnetTransactionRepository $repository,
        private readonly EncryptsPayload $encryptor,
        private readonly KnetConfig $config,
    ) {}

    /**
     * Decrypts and parses Knet response payload.
     *
     * @throws AccessDeniedHttpException
     */
    public function decryptAndParse(Request $request): array
    {
        $trandata = $this->extractTrandata($request);

        if (empty($trandata)) {
            logger()->error('KnetResponseService | No trandata in request', [
                'ip' => $request->ip(),
            ]);
            throw new AccessDeniedHttpException('Invalid Request: No trandata field found in KNet response');
        }

        try {
            $payload = $this->encryptor->decrypt($trandata, $this->config->getResourceKey());

            if (empty($payload)) {
                logger()->error('KnetResponseService | Empty payload after decryption');
                throw new AccessDeniedHttpException('Failed to decrypt KNet response: Empty payload after decryption');
            }

            parse_str($payload, $payloadArray);

            if (empty($payloadArray)) {
                logger()->error('KnetResponseService | Failed to parse decrypted payload');
                throw new AccessDeniedHttpException('Failed to parse KNet response: Invalid payload format');
            }

            if (empty($payloadArray['trackid'])) {
                logger()->error('KnetResponseService | Missing track ID in response');
                throw new AccessDeniedHttpException('Missing track ID in response: Invalid KNet response format');
            }

            if ($this->config->isDebugMode()) {
                logger()->debug('KnetResponseService | Decrypted KNet response', [
                    'track_id' => $payloadArray['trackid'],
                    'result' => $payloadArray['result'] ?? 'not_set',
                ]);
            }

            return $payloadArray;

        } catch (InvalidHexDataException $e) {
            logger()->error('KnetResponseService | Invalid hex data', [
                'error_message' => $e->getMessage(),
            ]);

            if (config('knet.debug_hex_conversion', false) && $e->getHexData()) {
                try {
                    logger()->debug('KnetResponseService | Hex analysis:', KPayClient::debugHexData($e->getHexData()));
                } catch (\Exception) {
                }
            }

            throw new AccessDeniedHttpException(
                'Invalid response data from KNet gateway: '.$e->getMessage(),
                $e
            );

        } catch (AccessDeniedHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            logger()->error('KnetResponseService | Unexpected error', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
            ]);

            throw new AccessDeniedHttpException(
                'Failed to process KNet response: '.$e->getMessage(),
                $e
            );
        }
    }

    private function extractTrandata(Request $request): ?string
    {
        $trandata = $request->input('trandata') ?? $request->get('trandata');

        if (! empty($trandata)) {
            return $trandata;
        }

        $rawContent = $request->getContent();

        if (! empty($rawContent) && str_contains($rawContent, 'trandata=')) {
            parse_str($rawContent, $parsedData);
            if (! empty($parsedData['trandata'])) {
                return $parsedData['trandata'];
            }
        }

        if (! empty($rawContent) && ctype_xdigit(trim($rawContent))) {
            return trim($rawContent);
        }

        return null;
    }
}

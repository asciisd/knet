<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\KPayClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class KnetResponseService
{
    /**
     * Decrypts and parses Knet response payload.
     *
     * @throws AccessDeniedHttpException
     */
    public static function decryptAndParse(Request $request): array
    {
        $trandata = $request->getContent();

        if (! $trandata) {
            throw new AccessDeniedHttpException('Invalid Request');
        }

        $payload = KPayClient::decryptAES($trandata, config('knet.resource_key'));

        parse_str($payload, $payloadArray);

        if (! isset($payloadArray['trackid'])) {
            throw new AccessDeniedHttpException('Missing track ID in response.');
        }

        return $payloadArray;
    }
}

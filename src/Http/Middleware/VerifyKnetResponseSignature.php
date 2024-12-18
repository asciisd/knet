<?php

namespace Asciisd\Knet\Http\Middleware;

use Asciisd\Knet\Exceptions\SignatureVerificationException;
use Asciisd\Knet\KnetResponseSignature;
use Asciisd\Knet\KPayClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyKnetResponseSignature
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $trandata = $request->getContent();

        // Decrypt Content and verify signature if exists
        if ($trandata) {
            $payload = KPayClient::decryptAES($trandata, config('knet.resource_key'));

            parse_str($payload, $payloadArray);

            try {
                KnetResponseSignature::verifyHeader($payload, $request->headers, $payloadArray['trackid']);
            } catch (SignatureVerificationException $exception) {
                throw new AccessDeniedHttpException($exception->getMessage(), $exception);
            }
        }

        return $next($request);
    }
}

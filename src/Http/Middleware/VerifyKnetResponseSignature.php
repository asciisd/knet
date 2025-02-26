<?php

namespace Asciisd\Knet\Http\Middleware;

use Asciisd\Knet\Exceptions\SignatureVerificationException;
use Asciisd\Knet\KnetResponseSignature;
use Asciisd\Knet\Services\KnetResponseService;
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
        logger()->info('Knet Response Signature Verification Middleware', [
            'content' => $request->getContent(),
        ]);

        try {
            $payloadArray = KnetResponseService::decryptAndParse($request);

            KnetResponseSignature::verifyHeader(
                http_build_query($payloadArray),
                $request->headers,
                $payloadArray['trackid']
            );

        } catch (SignatureVerificationException $exception) {
            logger()->error('Knet Response Signature Verification Failed', [
                'exception' => $exception,
            ]);
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }
}

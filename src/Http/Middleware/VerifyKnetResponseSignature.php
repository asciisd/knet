<?php

namespace Asciisd\Knet\Http\Middleware;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetResponseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyKnetResponseSignature
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        logger()->info('Knet Response Signature Verification Middleware', [
            'content' => $request->getContent(),
        ]);

        $payloadArray = KnetResponseService::decryptAndParse($request);

        if (! $this->isValidResponse($payloadArray)) {
            logger()->error('Knet Response Signature Verification Failed', $payloadArray);
            abort(403, 'Knet Response Signature Verification Failed');
        }

        return $next($request);
    }

    /**
     * Validate the response signature.
     *
     * @param array $payloadArray
     * @return bool
     */
    private function isValidResponse(array $payloadArray): bool
    {
        return isset($payloadArray['trackid']) && KnetTransaction::findByTrackId($payloadArray['trackid']) !== null;
    }
}

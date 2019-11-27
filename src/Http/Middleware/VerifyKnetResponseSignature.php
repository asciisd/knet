<?php

namespace Asciisd\Knet\Http\Middleware;

use Asciisd\Knet\Exceptions\SignatureVerificationException;
use Asciisd\Knet\KnetResponseSignature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyKnetResponseSignature
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            KnetResponseSignature::verifyHeader(
                $request->getContent(),
                $request->header(),
                $request->input('trackid')
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
        return $next($request);
    }
}
<?php

namespace Asciisd\Knet\Http\Middleware;

use Asciisd\Knet\Repositories\KnetTransactionRepository;
use Asciisd\Knet\Services\KnetResponseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyKnetResponseSignature
{
    public function __construct(
        private readonly KnetResponseService $responseService,
        private readonly KnetTransactionRepository $repository,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (config('knet.debug', false)) {
            logger()->debug('VerifyKnetResponseSignature | Incoming request', [
                'ip' => $request->ip(),
            ]);
        }

        $payloadArray = $this->responseService->decryptAndParse($request);

        if (! $this->isValidResponse($payloadArray)) {
            logger()->error('VerifyKnetResponseSignature | Signature verification failed');
            abort(403, 'Knet Response Signature Verification Failed');
        }

        $request->attributes->set('knet_payload', $payloadArray);

        return $next($request);
    }

    private function isValidResponse(array $payloadArray): bool
    {
        if (! isset($payloadArray['trackid'])) {
            return false;
        }

        try {
            $this->repository->findByTrackId($payloadArray['trackid']);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}

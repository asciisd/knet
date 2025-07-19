<?php

namespace Asciisd\Knet\Services;

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
     * Decrypts and parses Knet response payload.
     *
     * @throws AccessDeniedHttpException
     */
    public static function decryptAndParse(Request $request): array
    {
        // 1. Get the full request content
        $content = $request->getContent();

        // 2. Parse the content into an array
        parse_str($content, $output);

        // 3. Extract only the trandata field
        $trandata = $output['trandata'] ?? null;

        if (! $trandata) {
            throw new AccessDeniedHttpException('Invalid Request');
        }

        // 4. Decrypt only the trandata field
        $payload = KPayClient::decryptAES($trandata, config('knet.resource_key'));

        // 5. Parse the decrypted result
        parse_str($payload, $payloadArray);

        if (! isset($payloadArray['trackid'])) {
            throw new AccessDeniedHttpException('Missing track ID in response.');
        }

        return $payloadArray;
    }
}

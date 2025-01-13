<?php

namespace Asciisd\Knet;

use Illuminate\Http\Request;

class KPayResponseHandler extends KPayClient
{
    private ?string $error = null;
    private string $error_code = '';

    public static function make(array $payloadArray, Request $request): static
    {
        return new static($payloadArray, $request);
    }

    public function __construct(public array $transaction, Request $request)
    {
        logger('Request:', $request->all());
        logger()->info('Transaction:', $transaction);

        $this->transaction['paid'] = $this->transaction['result'] == 'CAPTURED';

        if ($request->has('error')) {
            $this->transaction['error_text'] = $request->input('ErrorText');
            $this->transaction['error'] = $request->input('Error');
            $this->transaction['rspcode'] = $request->input('rspcode');

            if (! $this->transaction['paid']) {
                $this->handleError($request->all());
            }
        }
    }

    public function handleError(array $error): void
    {
        $this->transaction['result'] = 'FAILED';
        $this->error_code = $error['Error'];
        $this->error = $error['ErrorText'];
    }

    private function decrypt($tranData): string
    {
        return $this->decryptAES($tranData, config('knet.resource_key'));
    }

    public function __toString()
    {
        return json_encode($this->transaction);
    }

    public function toArray(): array
    {
        return $this->transaction;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function hasErrors(): bool
    {
        return ! is_null($this->error);
    }

    public function errorCode(): string
    {
        return $this->error_code;
    }

    public function isDuplicated(): bool
    {
        return $this->error_code == 'IPAY0100114';
    }

    public function isInvalidPaymentStatus(): bool
    {
        return $this->error_code == 'IPAY0100055';
    }

    public function __get($name)
    {
        return $this->transaction[$name];
    }
}

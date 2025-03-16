<?php

namespace Asciisd\Knet\Services;

use Asciisd\Knet\Enums\Errors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KPayResponseHandler
{
    private array $transaction;
    private ?string $error = null;
    private string $error_code = '';
    private ?Errors $error_enum = null;

    /**
     * Factory method to create a new instance.
     */
    public static function make(array $payloadArray, Request $request): self
    {
        return new self($payloadArray, $request);
    }

    /**
     * KPayResponseHandler Constructor.
     */
    public function __construct(array $transaction, Request $request)
    {
        Log::info('KPayResponseHandler | Request Data:', $request->all());
        Log::info('KPayResponseHandler | Transaction Data:', $transaction);

        $this->transaction = $transaction;
        $this->setPaymentStatus();

        if ($request->has('error')) {
            $this->setTransactionError($request);
        }
    }

    /**
     * Sets the payment status based on the transaction result.
     */
    private function setPaymentStatus(): void
    {
        $this->transaction['paid'] = ($this->transaction['result'] ?? '') === 'CAPTURED';
    }

    /**
     * Handles errors in the transaction response.
     */
    private function setTransactionError(Request $request): void
    {
        $this->error_code = $request->input('Error', '');
        $this->error = $request->input('ErrorText', '');
        $this->transaction['rspcode'] = $request->input('rspcode', '');

        if (! $this->transaction['paid']) {
            $this->transaction['result'] = 'FAILED';
        }

        // Try to map the error code to an enum value
        if ($this->error_code) {
            try {
                $this->error_enum = constant(Errors::class.'::'.$this->error_code);
            } catch (\Throwable $e) {
                // If the error code doesn't match any enum value, leave error_enum as null
            }
        }
    }

    /**
     * Converts the transaction data to an array.
     */
    public function toArray(): array
    {
        return $this->transaction;
    }

    /**
     * Converts the transaction data to a JSON string.
     */
    public function __toString(): string
    {
        return json_encode($this->transaction, JSON_PRETTY_PRINT);
    }

    /**
     * Checks if the transaction has errors.
     */
    public function hasErrors(): bool
    {
        return ! empty($this->error);
    }

    /**
     * Gets the error message.
     */
    public function error(): ?string
    {
        if ($this->error_enum) {
            return $this->error_enum->description();
        }
        return $this->error;
    }

    /**
     * Gets the error code.
     */
    public function errorCode(): string
    {
        return $this->error_code;
    }

    /**
     * Gets the error enum if available.
     */
    public function errorEnum(): ?Errors
    {
        return $this->error_enum;
    }

    /**
     * Checks if the transaction is a duplicate.
     */
    public function isDuplicated(): bool
    {
        return $this->error_enum === Errors::IPAY0100114;
    }

    /**
     * Checks if the transaction has an invalid payment status.
     */
    public function isInvalidPaymentStatus(): bool
    {
        return $this->error_enum === Errors::IPAY0100055;
    }

    /**
     * Getter method for transaction properties.
     */
    public function getTransactionProperty(string $name)
    {
        return $this->transaction[$name] ?? null;
    }
}

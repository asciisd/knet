<?php

namespace Asciisd\Knet\DataTransferObjects;

use Asciisd\Knet\Exceptions\KnetException;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest
{
    public function __construct(
        public readonly Model $user,
        public readonly float $amount,
        public readonly ?string $trackId = null,
        public readonly ?string $udf1 = null,
        public readonly ?string $udf2 = null,
        public readonly ?string $udf3 = null,
        public readonly ?string $udf4 = null,
        public readonly ?string $udf5 = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->amount <= 0) {
            throw new KnetException('Payment amount must be greater than zero');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'trackid' => $this->trackId,
            'udf1' => $this->udf1,
            'udf2' => $this->udf2,
            'udf3' => $this->udf3,
            'udf4' => $this->udf4,
            'udf5' => $this->udf5,
        ]);
    }
} 
<?php

namespace Asciisd\Knet\DataTransferObjects;

class KnetResponse
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $trackId,
        public readonly string $result,
        public readonly ?string $auth,
        public readonly ?string $reference,
        public readonly ?string $transactionId,
        public readonly ?string $postDate,
        public readonly ?string $errorText,
        public readonly string $amount,
        public readonly bool $paid,
        public readonly ?string $avr,
        public readonly ?string $udf1,
        public readonly ?string $udf2,
        public readonly ?string $udf3,
        public readonly ?string $udf4,
        public readonly ?string $udf5,
        public readonly ?string $udf6,
        public readonly ?string $udf7,
        public readonly ?string $udf8,
        public readonly ?string $udf9,
        public readonly ?string $udf10,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: $data['paymentid'] ?? '',
            trackId: $data['trackid'] ?? '',
            result: $data['result'] ?? '',
            auth: $data['auth'] ?? null,
            reference: $data['ref'] ?? null,
            transactionId: $data['tranid'] ?? null,
            postDate: $data['postdate'] ?? null,
            errorText: $data['error_text'] ?? null,
            amount: $data['amt'] ?? '0.000',
            paid: ($data['result'] ?? '') === 'CAPTURED',
            avr: $data['avr'] ?? null,
            udf1: $data['udf1'] ?? null,
            udf2: $data['udf2'] ?? null,
            udf3: $data['udf3'] ?? null,
            udf4: $data['udf4'] ?? null,
            udf5: $data['udf5'] ?? null,
            udf6: $data['udf6'] ?? null,
            udf7: $data['udf7'] ?? null,
            udf8: $data['udf8'] ?? null,
            udf9: $data['udf9'] ?? null,
            udf10: $data['udf10'] ?? null,
        );
    }
} 
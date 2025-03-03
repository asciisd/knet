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
        public readonly float $amount,
        public readonly bool $paid,
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
            amount: (float) ($data['amt'] ?? 0),
            paid: ($data['result'] ?? '') === 'CAPTURED'
        );
    }
} 
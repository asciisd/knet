<?php

namespace Asciisd\Knet\Factories;

use Asciisd\Knet\DataTransferObjects\PaymentRequest;
use Asciisd\Knet\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentFactory
{
    public static function createRequest(
        Model $user,
        float $amount,
        array $options = []
    ): PaymentRequest {
        return new PaymentRequest(
            user: $user,
            amount: $amount,
            trackId: $options['trackid'] ?? Str::uuid(),
            udf1: $options['udf1'] ?? null,
            udf2: $options['udf2'] ?? null,
            udf3: $options['udf3'] ?? null,
            udf4: $options['udf4'] ?? null,
            udf5: $options['udf5'] ?? null,
        );
    }

    public static function mockSuccessfulResponse(string $trackId): array
    {
        return [
            'paymentid' => Str::random(20),
            'trackid' => $trackId,
            'result' => PaymentStatus::CAPTURED->value,
            'auth' => Str::random(6),
            'ref' => Str::random(10),
            'tranid' => Str::random(15),
            'postdate' => now()->format('Ymd'),
            'amt' => '100.000',
        ];
    }
} 
<?php

namespace Asciisd\Knet\Contracts;

interface InquiresPayments
{
    public function inquirePayment(float|string $amount, string $trackid): array;
}

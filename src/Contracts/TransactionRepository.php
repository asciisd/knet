<?php

namespace Asciisd\Knet\Contracts;

use Asciisd\Knet\KnetTransaction;

interface TransactionRepository
{
    public function create(array $data): KnetTransaction;

    public function findByTrackId(string $trackId): KnetTransaction;

    public function update(KnetTransaction $transaction, array $data): KnetTransaction;
}

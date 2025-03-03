<?php

namespace Asciisd\Knet\Repositories;

use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Exceptions\KnetException;

class KnetTransactionRepository
{
    public function create(array $data): KnetTransaction
    {
        return KnetTransaction::create($data);
    }

    public function findByTrackId(string $trackId): KnetTransaction
    {
        try {
            return KnetTransaction::where('trackid', $trackId)->firstOrFail();
        } catch (\Exception $e) {
            throw new KnetException("Transaction not found with track ID: {$trackId}");
        }
    }

    public function update(KnetTransaction $transaction, array $data): KnetTransaction
    {
        $transaction->update($data);
        return $transaction->fresh();
    }

    public function getRecentTransactions(int $limit = 10): array
    {
        return KnetTransaction::latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }
} 
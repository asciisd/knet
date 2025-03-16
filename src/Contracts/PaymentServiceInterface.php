<?php

namespace Asciisd\Knet\Contracts;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Database\Eloquent\Model;

interface PaymentServiceInterface
{
    /**
     * Create a new payment transaction
     */
    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction;
    
    /**
     * Handle the payment response
     */
    public function handlePaymentResponse(array $payload): KnetTransaction;

    /**
     * Inquire about a payment transaction
     */
    public function inquirePayment(float|string $amount, string $trackid): array;

    /**
     * Process a refund for a transaction
     *
     * @param KnetTransaction $transaction The transaction to refund
     * @param float|null $amount The amount to refund. If null, refunds the full amount
     * @return array The refund response
     * @throws \Illuminate\Http\Client\RequestException If the refund request fails
     */
    public function refundPayment(KnetTransaction $transaction, ?float $amount = null): array;
} 
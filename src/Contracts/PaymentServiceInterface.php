<?php

namespace Asciisd\Knet\Contracts;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Database\Eloquent\Model;

interface PaymentServiceInterface
{
    public function createPayment(Model $user, float $amount, array $options = []): KnetTransaction;
    
    public function handlePaymentResponse(array $payload): KnetTransaction;
} 
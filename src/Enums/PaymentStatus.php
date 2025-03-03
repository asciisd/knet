<?php

namespace Asciisd\Knet\Enums;

enum PaymentStatus: string
{
    case INITIATED = 'INITIATED';
    case CAPTURED = 'CAPTURED';
    case FAILED = 'FAILED';
    case PENDING = 'PENDING';
    
    public function isPaid(): bool
    {
        return $this === self::CAPTURED;
    }
    
    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }
    
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
} 
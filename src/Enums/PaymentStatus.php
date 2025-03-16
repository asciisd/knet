<?php

namespace Asciisd\Knet\Enums;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

enum PaymentStatus: string
{
    // Success statuses
    case SUCCESS = 'SUCCESS';
    case CAPTURED = 'CAPTURED';
    
    // Failure statuses
    case FAILED = 'FAILED';
    case NOT_CAPTURED = 'NOT CAPTURED';
    case ABANDONED = 'ABANDONED';
    case CANCELLED = 'CANCELLED';
    case DECLINED = 'DECLINED';
    case RESTRICTED = 'RESTRICTED';
    case VOID = 'VOID';
    case TIMEDOUT = 'TIMEDOUT';
    
    // Pending/Processing statuses
    case PENDING = 'PENDING';
    case UNKNOWN = 'UNKNOWN';
    case INITIATED = 'INITIATED';

    public const array SUCCESS_RESPONSES = ['SUCCESS', 'CAPTURED'];
    public const array NEED_MORE_ACTION = ['INITIATED', 'PENDING'];
    public const array FAILED_RESPONSES = [
        'ABANDONED', 'CANCELLED', 'FAILED', 'DECLINED', 'RESTRICTED', 
        'VOID', 'TIMEDOUT', 'UNKNOWN', 'NOT CAPTURED'
    ];

    public function isPaid(): bool
    {
        return $this->isSuccessful();
    }

    public function isSuccessful(): bool
    {
        return in_array($this->value, self::SUCCESS_RESPONSES);
    }
    
    public function isFailed(): bool
    {
        return in_array($this->value, self::FAILED_RESPONSES);
    }

    public function isPending(): bool
    {
        return in_array($this->value, self::NEED_MORE_ACTION);
    }

    public function needMoreAction(): bool
    {
        return $this->isPending();
    }

    public function imageUrl(): string
    {
        $status = Str::lower($this->name);
        return URL::to("vendor/knet/img/invoice/status/{$status}.png");
    }

    public function displayName(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::INITIATED => 'Initiated',
            self::CAPTURED => 'Captured',
            self::ABANDONED => 'Abandoned',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
            self::DECLINED => 'Declined',
            self::RESTRICTED => 'Restricted',
            self::VOID => 'Void',
            self::TIMEDOUT => 'Timedout',
            self::UNKNOWN => 'Unknown',
            self::NOT_CAPTURED => 'Not Captured',
            self::PENDING => 'Pending',
        };
    }

    public function styleColor(): string
    {
        return match ($this) {
            self::SUCCESS, self::CAPTURED => 'success-status',
            self::INITIATED, self::UNKNOWN, self::PENDING => 'info-status',
            default => 'danger-status',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::SUCCESS, self::CAPTURED => 'successText',
            self::INITIATED, self::UNKNOWN, self::PENDING => 'infoText',
            default => 'dangerText',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::SUCCESS, self::CAPTURED => 'successBG',
            self::INITIATED, self::UNKNOWN, self::PENDING => 'infoBG',
            default => 'dangerBG',
        };
    }

    public static function successStates(): array
    {
        return array_column([
            self::SUCCESS,
            self::CAPTURED,
        ], 'name');
    }

    public static function successStatesValues(): array
    {
        return array_column([
            self::SUCCESS,
            self::CAPTURED,
        ], 'value');
    }

    public static function failedStates(): array
    {
        return array_column([
            self::ABANDONED, self::CANCELLED, self::FAILED, self::DECLINED, 
            self::RESTRICTED, self::VOID, self::TIMEDOUT, self::NOT_CAPTURED
        ], 'name');
    }

    public static function loadingStates(): array
    {
        return array_column([
            self::INITIATED, self::UNKNOWN, self::PENDING
        ], 'name');
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->displayName(),
            'style' => $this->styleColor(),
            'text_color' => $this->textColor(),
            'bg_color' => $this->bgColor(),
        ];
    }
} 
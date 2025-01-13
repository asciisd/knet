<?php

namespace Asciisd\Knet\Enums;

use Illuminate\Support\Str;

enum Status: string
{
    case CAPTURED = 'CAPTURED';
    case ABANDONED = 'ABANDONED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case RESTRICTED = 'RESTRICTED';
    case VOID = 'VOID';
    case TIMEDOUT = 'TIMEDOUT';
    case UNKNOWN = 'UNKNOWN';
    case NOT_CAPTURED = 'NOT CAPTURED';
    case INITIATED = 'INITIATED';

    public const array SUCCESS_RESPONSES = ['CAPTURED'];
    public const array NEED_MORE_ACTION = ['INITIATED'];
    public const array FAILED_RESPONSES = [
        'ABANDONED', 'CANCELLED', 'FAILED', 'DECLINED', 'RESTRICTED', 'VOID', 'TIMEDOUT', 'UNKNOWN', 'NOT CAPTURED'
    ];

    public function isSuccessful(): bool
    {
        return in_array($this, self::SUCCESS_RESPONSES);
    }

    public function isFailed(): bool
    {
        return in_array($this, self::FAILED_RESPONSES);
    }

    public function needMoreAction(): bool
    {
        return in_array($this, self::NEED_MORE_ACTION);
    }

    public function icon(): string
    {
        $status = Str::lower($this->value);

        return url("vendor/knet/img/invoice/status/{$status}.png");
    }

    public function displayName(): string
    {
        return match ($this) {
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
        };
    }

    public function styleColor(): string
    {
        return match ($this) {
            self::CAPTURED => 'success-status',
            self::INITIATED, self::UNKNOWN => 'info-status',
            self::ABANDONED, self::CANCELLED, self::FAILED, self::DECLINED, self::RESTRICTED, self::VOID, self::TIMEDOUT, self::NOT_CAPTURED => 'danger-status',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::CAPTURED => 'successText',
            self::INITIATED, self::UNKNOWN => 'infoText',
            self::ABANDONED, self::CANCELLED, self::FAILED, self::DECLINED, self::RESTRICTED, self::VOID, self::TIMEDOUT, self::NOT_CAPTURED => 'dangerText',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::CAPTURED => 'successBG',
            self::INITIATED, self::UNKNOWN => 'infoBG',
            self::ABANDONED, self::CANCELLED, self::FAILED, self::DECLINED, self::RESTRICTED, self::VOID, self::TIMEDOUT, self::NOT_CAPTURED => 'dangerBG',
        };
    }

    public static function successStates(): array
    {
        return array_column([
            self::CAPTURED,
        ], 'name');
    }

    public static function successStatesValues(): array
    {
        return array_column([
            self::CAPTURED,
        ], 'value');
    }

    public static function failedStates(): array
    {
        return array_column([
            self::ABANDONED, self::CANCELLED, self::FAILED, self::DECLINED, self::RESTRICTED, self::VOID, self::TIMEDOUT, self::NOT_CAPTURED
        ], 'name');
    }

    public static function loadingStates(): array
    {
        return array_column([
            self::INITIATED, self::UNKNOWN,
        ], 'name');
    }

    public function isSuccess(): bool
    {
        return in_array($this->name, self::successStates());
    }

    public function isPending(): bool
    {
        return in_array($this->name, self::loadingStates());
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

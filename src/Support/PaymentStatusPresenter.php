<?php

namespace Asciisd\Knet\Support;

use Asciisd\Knet\Enums\PaymentStatus;

class PaymentStatusPresenter
{
    public function __construct(private readonly PaymentStatus $status) {}

    public function styleColor(): string
    {
        return match ($this->status) {
            PaymentStatus::SUCCESS, PaymentStatus::CAPTURED => 'success-status',
            PaymentStatus::INITIATED, PaymentStatus::UNKNOWN, PaymentStatus::PENDING => 'info-status',
            default => 'danger-status',
        };
    }

    public function textColor(): string
    {
        return match ($this->status) {
            PaymentStatus::SUCCESS, PaymentStatus::CAPTURED => 'successText',
            PaymentStatus::INITIATED, PaymentStatus::UNKNOWN, PaymentStatus::PENDING => 'infoText',
            default => 'dangerText',
        };
    }

    public function bgColor(): string
    {
        return match ($this->status) {
            PaymentStatus::SUCCESS, PaymentStatus::CAPTURED => 'successBG',
            PaymentStatus::INITIATED, PaymentStatus::UNKNOWN, PaymentStatus::PENDING => 'infoBG',
            default => 'dangerBG',
        };
    }

    public function imageUrl(): string
    {
        $status = strtolower($this->status->name);

        return url("vendor/knet/img/invoice/status/{$status}.png");
    }

    public function toArray(): array
    {
        return [
            'id' => $this->status->slug(),
            'name' => $this->status->displayName(),
            'style' => $this->styleColor(),
            'text_color' => $this->textColor(),
            'bg_color' => $this->bgColor(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Asciisd\Knet\Cashier;

use Asciisd\CashierCore\Contracts\PaymentAdapterInterface;
use Asciisd\CashierCore\DataObjects\PaymentMethodSnapshot;
use Asciisd\CashierCore\DataObjects\PaymentResult;
use Asciisd\CashierCore\DataObjects\TransactionWebhookUpdate;
use Asciisd\CashierCore\Enums\PaymentMethodBrand;
use Asciisd\CashierCore\Enums\PaymentMethodType;
use Asciisd\CashierCore\Enums\PaymentStatus;
use Asciisd\Knet\Enums\PaymentStatus as KnetPaymentStatus;
use Asciisd\Knet\KnetTransaction;

/**
 * Maps KNET transactions and callback payloads to cashier-core DTOs.
 *
 * Amounts cross this boundary in cents: cashier-core works in integer minor
 * units while KnetTransaction stores decimal KWD in `amt`.
 */
class KnetAdapter implements PaymentAdapterInterface
{
    /**
     * Transform a KnetTransaction into a PaymentResult.
     */
    public function fromProviderResponse(mixed $response): PaymentResult
    {
        /** @var KnetTransaction $response */
        $knetStatus = KnetPaymentStatus::tryFrom($response->result ?? 'INITIATED');
        $status = $this->mapStatus($knetStatus);

        return new PaymentResult(
            success: $knetStatus?->isSuccessful() ?? false,
            transactionId: $response->trackid,
            status: $status,
            amount: (int) ($response->amt * 100),
            currency: 'KWD',
            message: $response->error_text,
            metadata: $this->buildMetadataFromTransaction($response),
            processorResponse: $response->toArray(),
        );
    }

    /**
     * Transform a raw KNET inquiry payload into a PaymentResult.
     */
    public function fromProviderPayload(string $transactionId, array $payload): PaymentResult
    {
        $result = $payload['result'] ?? 'UNKNOWN';
        $knetStatus = KnetPaymentStatus::tryFrom($result);
        $status = $this->mapStatus($knetStatus);

        return new PaymentResult(
            success: $knetStatus?->isSuccessful() ?? false,
            transactionId: $transactionId,
            status: $status,
            amount: (int) (($payload['amt'] ?? 0) * 100),
            currency: 'KWD',
            message: $payload['error_text'] ?? null,
            metadata: [
                'knet_payment_id' => $payload['paymentid'] ?? null,
                'knet_transaction_id' => $payload['tranid'] ?? null,
                'knet_ref' => $payload['ref'] ?? null,
                'knet_auth' => $payload['auth'] ?? null,
            ],
            processorResponse: $payload,
            paymentMethodSnapshot: $this->extractPaymentMethodFromPayload($payload),
        );
    }

    /**
     * Transform a KNET response callback payload into a TransactionWebhookUpdate.
     */
    public function fromWebhook(array $payload): TransactionWebhookUpdate
    {
        $result = $payload['result'] ?? 'UNKNOWN';
        $knetStatus = KnetPaymentStatus::tryFrom($result);
        $status = $this->mapStatus($knetStatus);

        return new TransactionWebhookUpdate(
            status: $status,
            processorResponse: $payload,
            paymentMethodSnapshot: $this->extractPaymentMethodFromPayload($payload),
            metadata: [
                'knet_payment_id' => $payload['paymentid'] ?? null,
                'knet_transaction_id' => $payload['tranid'] ?? null,
                'knet_ref' => $payload['ref'] ?? null,
                'knet_auth' => $payload['auth'] ?? null,
                'knet_postdate' => $payload['postdate'] ?? null,
            ],
            errorMessage: $payload['error_text'] ?? null,
            amount: isset($payload['amt']) ? (int) ((float) $payload['amt'] * 100) : null,
            currency: 'KWD',
        );
    }

    /**
     * Map KNET PaymentStatus to cashier-core PaymentStatus.
     */
    public function mapStatus(mixed $providerStatus): PaymentStatus
    {
        if ($providerStatus instanceof KnetPaymentStatus) {
            return match ($providerStatus) {
                KnetPaymentStatus::SUCCESS, KnetPaymentStatus::CAPTURED => PaymentStatus::Succeeded,
                KnetPaymentStatus::FAILED, KnetPaymentStatus::NOT_CAPTURED,
                KnetPaymentStatus::DECLINED, KnetPaymentStatus::RESTRICTED,
                KnetPaymentStatus::VOID, KnetPaymentStatus::TIMEDOUT,
                KnetPaymentStatus::ABANDONED => PaymentStatus::Failed,
                KnetPaymentStatus::CANCELLED => PaymentStatus::Canceled,
                KnetPaymentStatus::INITIATED => PaymentStatus::RequiresAction,
                KnetPaymentStatus::PENDING, KnetPaymentStatus::UNKNOWN => PaymentStatus::Pending,
            };
        }

        $statusStr = strtoupper((string) $providerStatus);

        return match ($statusStr) {
            'SUCCESS', 'CAPTURED' => PaymentStatus::Succeeded,
            'FAILED', 'NOT CAPTURED', 'DECLINED', 'RESTRICTED', 'VOID', 'TIMEDOUT', 'ABANDONED' => PaymentStatus::Failed,
            'CANCELLED' => PaymentStatus::Canceled,
            'INITIATED' => PaymentStatus::RequiresAction,
            default => PaymentStatus::Pending,
        };
    }

    public function getProviderName(): string
    {
        return 'knet';
    }

    private function buildMetadataFromTransaction(KnetTransaction $transaction): array
    {
        $metadata = [
            'redirect_url' => $transaction->url,
            'knet_payment_id' => $transaction->paymentid,
            'knet_transaction_id' => $transaction->tranid,
            'knet_ref' => $transaction->ref,
            'knet_auth' => $transaction->auth,
            'knet_track_id' => $transaction->trackid,
        ];

        return array_filter($metadata, fn ($v) => $v !== null);
    }

    private function extractPaymentMethodFromPayload(array $payload): ?PaymentMethodSnapshot
    {
        $cardNumber = $payload['card_number'] ?? null;
        if (! $cardNumber) {
            return new PaymentMethodSnapshot(
                type: PaymentMethodType::DigitalWallet,
                brand: PaymentMethodBrand::Other,
                displayName: 'Knet',
            );
        }

        $lastFour = substr($cardNumber, -4);

        return new PaymentMethodSnapshot(
            type: PaymentMethodType::CreditCard,
            brand: PaymentMethodBrand::Other,
            lastFour: $lastFour,
            displayName: "Knet •••• {$lastFour}",
        );
    }
}

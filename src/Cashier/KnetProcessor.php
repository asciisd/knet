<?php

declare(strict_types=1);

namespace Asciisd\Knet\Cashier;

use Asciisd\CashierCore\Contracts\CustomerContract;
use Asciisd\CashierCore\Contracts\PaymentProcessorInterface;
use Asciisd\CashierCore\Contracts\PreparesChargeData;
use Asciisd\CashierCore\Contracts\ProvidesWebhookTransactionId;
use Asciisd\CashierCore\DataObjects\PaymentResult;
use Asciisd\CashierCore\DataObjects\RefundResult;
use Asciisd\CashierCore\DataObjects\TransactionWebhookUpdate;
use Asciisd\CashierCore\Enums\RefundStatus;
use Asciisd\CashierCore\Exceptions\PaymentProcessingException;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Services\KnetPaymentService;
use Illuminate\Database\Eloquent\Model;

/**
 * KNET processor for cashier-core v2.
 *
 * Wraps KnetPaymentService behind the cashier-core processor contract:
 * `charge()` creates a KNET payment and surfaces the gateway redirect URL,
 * results arrive via the package's own response callback (KnetPaymentSucceeded
 * / KnetPaymentFailed events), and refunds/inquiry delegate to the service.
 *
 * KNET charges in KWD only, and its payment initiation needs the payable
 * Eloquent model itself — both are injected by `prepareChargeData()` so the
 * cashier-core PaymentService needs no KNET-specific branch.
 */
class KnetProcessor implements PaymentProcessorInterface, PreparesChargeData, ProvidesWebhookTransactionId
{
    private KnetPaymentService $paymentService;

    private KnetAdapter $adapter;

    /** @var string[] */
    private array $supportedFeatures = ['charge', 'refund', 'inquiry'];

    public function __construct(array $config = [])
    {
        try {
            $this->paymentService = app(KnetPaymentService::class);
            $this->adapter = new KnetAdapter;
        } catch (\Exception $e) {
            throw new PaymentProcessingException('Knet provider not available: '.$e->getMessage());
        }
    }

    /**
     * KNET-specific charge shaping: the payment initiation service needs the
     * payable model (`HasKnet` host user), and the gateway settles KWD only.
     */
    public function prepareChargeData(CustomerContract $customer, string $connection, array $paymentData): array
    {
        if ($customer instanceof Model) {
            $paymentData['user'] = $customer;
        }

        $paymentData['currency'] = 'KWD';

        return $paymentData;
    }

    public function extractWebhookTransactionId(array $payload): ?string
    {
        $trackId = $payload['trackid'] ?? null;

        return $trackId === null || $trackId === '' ? null : (string) $trackId;
    }

    public function charge(array $data): PaymentResult
    {
        $user = $data['user'] ?? null;
        if (! $user instanceof Model) {
            throw new PaymentProcessingException('User is required for Knet payments');
        }

        $amount = ($data['amount'] ?? 0) / 100;

        $options = array_filter([
            'udf1' => $data['metadata']['user_id'] ?? null,
            'udf2' => $data['metadata']['user_email'] ?? null,
            'udf4' => $data['metadata']['trading_account_login'] ?? null,
            'udf5' => $this->sanitizeUdf($data['description'] ?? null),
        ]);

        $knetTransaction = $this->paymentService->createPayment($user, $amount, $options);

        return $this->adapter->fromProviderResponse($knetTransaction);
    }

    public function refund(string $transactionId, ?int $amount = null): RefundResult
    {
        $knetTransaction = KnetTransaction::where('trackid', $transactionId)->firstOrFail();

        $refundAmount = $amount ? $amount / 100 : null;
        $result = $this->paymentService->refundPayment($knetTransaction, $refundAmount);

        $success = ($result['status'] ?? '') === 'success'
            || ($result['result'] ?? '') === 'SUCCESS';

        return new RefundResult(
            success: $success,
            refundId: $result['refund_id'] ?? $transactionId,
            originalTransactionId: $transactionId,
            status: $success ? RefundStatus::Succeeded : RefundStatus::Failed,
            amount: $amount ?? (int) ($knetTransaction->amt * 100),
            currency: 'KWD',
            message: $result['message'] ?? null,
            metadata: $result,
        );
    }

    public function capture(string $transactionId, ?int $amount = null): PaymentResult
    {
        throw new \BadMethodCallException('Knet does not support capture.');
    }

    public function authorize(array $data): PaymentResult
    {
        throw new \BadMethodCallException('Knet does not support authorize.');
    }

    public function void(string $transactionId): PaymentResult
    {
        throw new \BadMethodCallException('Knet does not support void.');
    }

    public function retrieve(string $transactionId): ?PaymentResult
    {
        $knetTransaction = KnetTransaction::where('trackid', $transactionId)->first();
        if (! $knetTransaction) {
            return null;
        }

        $updated = $this->paymentService->inquireAndUpdateTransaction($knetTransaction);

        return $this->adapter->fromProviderResponse($updated);
    }

    public function getPaymentStatus(string $transactionId): string
    {
        $knetTransaction = KnetTransaction::where('trackid', $transactionId)->firstOrFail();

        return $knetTransaction->result ?? 'UNKNOWN';
    }

    public function validatePaymentData(array $data): array
    {
        $rules = [
            'amount' => 'required|integer|min:1',
            'user' => 'required',
        ];

        return validator($data, $rules)->validate();
    }

    public function parseWebhook(array $payload): TransactionWebhookUpdate
    {
        return $this->adapter->fromWebhook($payload);
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        return false;
    }

    /**
     * Get the adapter instance.
     */
    public function getAdapter(): KnetAdapter
    {
        return $this->adapter;
    }

    /**
     * Sanitize a value for KNET UDF fields (alphanumeric, spaces, dashes only; max 40 chars).
     */
    private function sanitizeUdf(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9 \-]/', '', $value);

        return mb_substr(trim($sanitized), 0, 40) ?: null;
    }

    public function getName(): string
    {
        return 'knet';
    }

    public function supports(string $feature): bool
    {
        return in_array($feature, $this->supportedFeatures);
    }
}

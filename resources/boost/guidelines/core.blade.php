## Asciisd Knet

This package provides an expressive, fluent interface to Kuwait's KNET payment gateway for Laravel applications. It handles payment initiation, encrypted callback processing, transaction inquiry, and refunds.

### Installation & Setup

1. Install via Composer: `composer require asciisd/knet`
2. Run `php artisan knet:install` to publish config, run migrations, and validate credentials.
3. Set required environment variables: `KNET_TRANSPORT_ID`, `KNET_TRANSPORT_PASSWORD`, `KNET_RESOURCE_KEY`.
4. Add the `HasKnet` trait to your payable model (typically `User`).

### Key Conventions

- Amounts are always in KWD with 3 decimal places (e.g. `10.000`).
- The currency code `414` is the ISO code for Kuwaiti Dinar.
- Track IDs must be unique per transaction and are auto-generated if not provided.
- The package registers routes under the `/knet` prefix by default (configurable via `KNET_PATH`).
- KNET callbacks are encrypted with AES-128-CBC; the package handles decryption automatically.

### Making Payments

Add the `HasKnet` trait to your model, then call `pay()`:

@verbatim
<code-snippet name="Making a payment via the HasKnet trait" lang="php">
use Asciisd\Knet\HasKnet;

class User extends Authenticatable
{
    use HasKnet;
}

// Create a payment and redirect the user
$transaction = $user->pay(10.000, [
    'udf1' => 'order_123',
    'udf2' => 'custom_data',
]);

return redirect($transaction->url);
</code-snippet>
@endverbatim

Or use the service directly:

@verbatim
<code-snippet name="Using KnetPaymentService directly" lang="php">
use Asciisd\Knet\Services\KnetPaymentService;

public function checkout(KnetPaymentService $paymentService)
{
    $transaction = $paymentService->createPayment(auth()->user(), 25.500, [
        'udf1' => 'invoice_456',
    ]);

    return redirect($transaction->url);
}
</code-snippet>
@endverbatim

### Handling Payment Events

Listen for these events to react to payment outcomes:

@verbatim
<code-snippet name="Listening for payment events" lang="php">
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\Events\KnetPaymentFailed;

// In EventServiceProvider or via Event::listen()
Event::listen(KnetPaymentSucceeded::class, function ($event) {
    $transaction = $event->transaction;
    // Mark order as paid, send confirmation email, etc.
});

Event::listen(KnetPaymentFailed::class, function ($event) {
    $transaction = $event->transaction;
    $error = $event->errorMessage;
    // Handle failure
});
</code-snippet>
@endverbatim

### Available Events

| Event | Payload | When |
|---|---|---|
| `KnetPaymentSucceeded` | `KnetTransaction $transaction` | Payment captured successfully |
| `KnetPaymentFailed` | `KnetTransaction $transaction, ?string $errorMessage` | Payment failed |
| `KnetResponseReceived` | `array $payload` | Raw KNET response decrypted |
| `KnetResponseHandled` | `array $payload` | Response fully processed |
| `KnetTransactionCreated` | `KnetTransaction $transaction` | Transaction model created |
| `KnetTransactionUpdated` | `KnetTransaction $transaction` | Transaction model updated |
| `KnetTransactionHasErrors` | `KnetTransaction $transaction` | Transaction encountered errors |
| `KnetReceiptSeen` | `KnetTransaction $transaction` | Receipt viewed |

### Payment Statuses

Use the `PaymentStatus` enum (`Asciisd\Knet\Enums\PaymentStatus`) to check transaction results:

- **Success:** `CAPTURED`, `SUCCESS`
- **Failed:** `FAILED`, `NOT_CAPTURED`, `ABANDONED`, `CANCELLED`, `DECLINED`, `RESTRICTED`, `VOID`, `TIMEDOUT`
- **Pending:** `PENDING`, `INITIATED`, `UNKNOWN`

### Transaction Inquiry & Refunds

@verbatim
<code-snippet name="Inquiry and refund operations" lang="php">
use Asciisd\Knet\Services\KnetPaymentService;

// Inquire about a transaction's current status
$transaction = $paymentService->inquireAndUpdateTransaction($transaction);

// Full refund
$result = $paymentService->refundPayment($transaction);

// Partial refund
$result = $paymentService->refundPayment($transaction, 5.000);
</code-snippet>
@endverbatim

### Configuration

Publish config via `php artisan knet:publish --config`. Key settings:

| Setting | Env Variable | Default |
|---|---|---|
| Transport ID | `KNET_TRANSPORT_ID` | — |
| Transport Password | `KNET_TRANSPORT_PASSWORD` | — |
| Resource Key | `KNET_RESOURCE_KEY` | — |
| Currency Code | `KNET_CURRENCY` | `414` |
| Language | `KNET_LANGUAGE` | `EN` |
| Redirect URL | `KNET_REDIRECT_URL` | `/` |
| Debug Mode | `KNET_DEBUG` | `false` |
| Route Path | `KNET_PATH` | `knet` |

### Routes

The package registers these routes automatically (disable with `Knet::ignoreRoutes()`):

| Method | URI | Name | Purpose |
|---|---|---|---|
| POST | `/knet/response` | `knet.response.store` | KNET callback endpoint |
| POST | `/knet/handle` | `knet.handle` | Post-payment redirect |
| POST/GET | `/knet/error` | `knet.error` | Error handling |

### Customization

- Use `Knet::ignoreMigrations()` in a service provider to skip package migrations.
- Use `Knet::ignoreRoutes()` to disable automatic route registration.
- Use `KnetTransaction::useCustomerModel(YourModel::class)` to change the billable model.
- Use UDF fields (`udf1` through `udf5`) to pass custom data through the payment flow.

### Artisan Commands

| Command | Purpose |
|---|---|
| `knet:install` | Full setup: publish config, run migrations, validate |
| `knet:check` | Validate credentials and configuration |
| `knet:publish` | Publish config (`--config`) and/or migrations (`--migrations`) |

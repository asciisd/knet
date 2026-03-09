---
name: knet-development
description: Build and work with KNET payment gateway integration, including payment initiation, callback handling, refunds, and event-driven payment flows.
---

# KNET Payment Development

## When to use this skill

Use this skill when:
- Integrating KNET payments into a Laravel application
- Creating payment flows that redirect users to the KNET gateway
- Handling KNET payment callbacks and response processing
- Implementing refunds (full or partial) for KNET transactions
- Querying transaction status from the KNET gateway
- Listening to payment events to trigger business logic
- Working with the `knet_transactions` table or `KnetTransaction` model

## Package Overview

`asciisd/knet` provides a fluent Laravel interface to Kuwait's KNET payment gateway. It handles AES-encrypted communication, payment initiation, callback verification, transaction inquiry, and refunds.

## Setup

### Environment Variables

```env
KNET_TRANSPORT_ID=your_transport_id
KNET_TRANSPORT_PASSWORD=your_password
KNET_RESOURCE_KEY=your_resource_key
KNET_REDIRECT_URL=/payment/success
KNET_CURRENCY=414
KNET_LANGUAGE=EN
KNET_DEBUG=false
```

### Add the Trait

Add `HasKnet` to any model that can make payments (typically `User`):

```php
use Asciisd\Knet\HasKnet;

class User extends Authenticatable
{
    use HasKnet;
}
```

This adds:
- `pay(float $amount, array $options = []): KnetTransaction` — create a payment
- `knet_transactions(): HasMany` — relationship to all transactions

## Creating Payments

### Via the Trait

```php
$transaction = $user->pay(10.000, [
    'udf1' => 'order_123',
    'udf2' => 'product_subscription',
]);

return redirect($transaction->url);
```

### Via Service Injection

```php
use Asciisd\Knet\Services\KnetPaymentService;

public function checkout(KnetPaymentService $paymentService)
{
    $transaction = $paymentService->createPayment(
        user: auth()->user(),
        amount: 25.500,
        options: [
            'udf1' => 'invoice_789',
            'trackid' => 'custom-track-id', // optional, auto-generated if omitted
        ]
    );

    return redirect($transaction->url);
}
```

### Important Rules

- Amounts MUST use 3 decimal places (KWD format): `10.000`, `25.500`, `0.250`
- Currency code `414` is the ISO code for Kuwaiti Dinar
- Track IDs must be unique per transaction
- UDF fields (`udf1`–`udf5`) carry custom data through the full payment lifecycle
- The `$transaction->url` contains the full KNET gateway URL to redirect the user to

## Payment Flow

1. **Initiate:** Call `$user->pay()` or `KnetPaymentService::createPayment()` — returns a `KnetTransaction` with a `url`
2. **Redirect:** Send the user to `$transaction->url` (the KNET payment page)
3. **Callback:** KNET POSTs encrypted `trandata` to `/knet/response` — the package decrypts and processes it automatically
4. **Events:** `KnetPaymentSucceeded` or `KnetPaymentFailed` is dispatched
5. **Redirect back:** User is redirected to `KNET_REDIRECT_URL` with the result

## Handling Payment Events

```php
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\Events\KnetPaymentFailed;
use Illuminate\Support\Facades\Event;

// In a service provider or EventServiceProvider
Event::listen(KnetPaymentSucceeded::class, function ($event) {
    $transaction = $event->transaction;
    // Fulfill the order, send confirmation, etc.
});

Event::listen(KnetPaymentFailed::class, function ($event) {
    $transaction = $event->transaction;
    $error = $event->errorMessage;
    // Notify user, log failure, etc.
});
```

### All Available Events

| Event | Payload |
|---|---|
| `KnetPaymentSucceeded` | `KnetTransaction $transaction` |
| `KnetPaymentFailed` | `KnetTransaction $transaction, ?string $errorMessage` |
| `KnetResponseReceived` | `array $payload` |
| `KnetResponseHandled` | `array $payload` |
| `KnetTransactionCreated` | `KnetTransaction $transaction` |
| `KnetTransactionUpdated` | `KnetTransaction $transaction` |
| `KnetTransactionHasErrors` | `KnetTransaction $transaction` |
| `KnetReceiptSeen` | `KnetTransaction $transaction` |

## Working with KnetTransaction

```php
use Asciisd\Knet\KnetTransaction;

// Find by track ID
$transaction = KnetTransaction::findByTrackId('track-123');

// Check status
$transaction->isCaptured();   // true if result == 'CAPTURED'
$transaction->hasStatus();    // true if result is not empty
$transaction->isRefundable(); // true if captured and not yet refunded

// Get amount
$transaction->rawAmount();       // float, e.g. 10.0
$transaction->formattedAmount(); // string, e.g. "10.000"

// Access relationship
$transaction->owner; // the User (or custom model) who made the payment
```

### Key Columns

| Column | Type | Description |
|---|---|---|
| `trackid` | string | Unique tracking identifier |
| `paymentid` | string | KNET payment ID |
| `result` | string | Payment result (CAPTURED, FAILED, etc.) |
| `amt` | string | Payment amount |
| `auth` | string | Authorization code |
| `ref` | string | Reference number |
| `tranid` | string | Transaction ID |
| `udf1`–`udf5` | string | User-defined fields |
| `paid` | boolean | Whether payment was successful |
| `refunded` | boolean | Whether transaction was refunded |
| `refund_amount` | decimal | Refund amount (if partial) |

## Payment Status Enum

```php
use Asciisd\Knet\Enums\PaymentStatus;

$status = PaymentStatus::from($transaction->result);

$status->isSuccessful(); // CAPTURED or SUCCESS
$status->isFailed();     // FAILED, ABANDONED, CANCELLED, DECLINED, etc.
$status->isPending();    // INITIATED, PENDING, UNKNOWN
$status->displayName();  // Human-readable name
```

## Inquiry & Refunds

### Transaction Inquiry

```php
use Asciisd\Knet\Services\KnetPaymentService;

// Re-check transaction status with KNET gateway and update locally
$transaction = $paymentService->inquireAndUpdateTransaction($transaction);

// Raw inquiry (returns array)
$result = $paymentService->inquirePayment(10.000, 'track-123');
```

### Refunds

```php
// Full refund
$result = $paymentService->refundPayment($transaction);

// Partial refund (5 KWD of a 10 KWD transaction)
$result = $paymentService->refundPayment($transaction, 5.000);
```

Only captured, non-refunded transactions can be refunded. Check with `$transaction->isRefundable()`.

## Routes

The package auto-registers these routes (prefix configurable via `KNET_PATH`):

| Method | URI | Purpose |
|---|---|---|
| POST | `/knet/response` | KNET encrypted callback (with signature verification middleware) |
| POST | `/knet/handle` | Post-success redirect handler |
| GET/POST | `/knet/error` | Error callback handler |

Disable auto-registration with `Knet::ignoreRoutes()` in a service provider.

## Customization

### Custom Billable Model

```php
use Asciisd\Knet\KnetTransaction;

// In AppServiceProvider::boot()
KnetTransaction::useCustomerModel(\App\Models\Customer::class);
```

### Skip Package Migrations

```php
use Asciisd\Knet\Knet;

// In AppServiceProvider::register()
Knet::ignoreMigrations();
```

### Skip Package Routes

```php
use Asciisd\Knet\Knet;

// In AppServiceProvider::register()
Knet::ignoreRoutes();
```

## Artisan Commands

| Command | Purpose |
|---|---|
| `php artisan knet:install` | Publish config, run migrations, validate setup |
| `php artisan knet:check` | Validate credentials and configuration |
| `php artisan knet:publish` | Publish config (`--config`) and/or migrations (`--migrations`) |

## Common Patterns

### Complete Checkout Controller

```php
use Asciisd\Knet\Services\KnetPaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly KnetPaymentService $paymentService
    ) {}

    public function pay(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.001']);

        $transaction = $this->paymentService->createPayment(
            $request->user(),
            $request->amount,
            ['udf1' => $request->order_id]
        );

        return redirect($transaction->url);
    }
}
```

### Payment Success Listener

```php
use Asciisd\Knet\Events\KnetPaymentSucceeded;

class HandleSuccessfulPayment
{
    public function handle(KnetPaymentSucceeded $event): void
    {
        $transaction = $event->transaction;
        $orderId = $transaction->udf1;

        Order::where('id', $orderId)->update(['status' => 'paid']);
    }
}
```

---
name: knet-development
description: Build and work with KNET payment gateway integration (asciisd/knet v8), including payment initiation, callback handling, refunds, event-driven payment flows, and the cashier-core v2 KnetProcessor/KnetAdapter driver.
---

# KNET Payment Development (v8)

## When to use this skill

Use this skill when:

- Integrating KNET payments into a Laravel application
- Creating payment flows that redirect users to the KNET gateway
- Handling KNET payment callbacks and response processing
- Implementing refunds (full or partial) for KNET transactions
- Querying transaction status from the KNET gateway
- Listening to payment events to trigger business logic
- Working with the `knet_transactions` table or `KnetTransaction` model
- Wiring KNET into `asciisd/cashier-core` v2 as the `knet` driver

## Package Overview

`asciisd/knet` provides a fluent Laravel interface to Kuwait's KNET payment gateway. It handles AES-encrypted communication, payment initiation, callback verification, transaction inquiry, and refunds.

**Requires:** PHP ^8.3, Laravel ^11|^12|^13 (Laravel 10 dropped in v8), `asciisd/cashier-core ^2.0`.
`Knet::VERSION` is `8.0.0`.

### Two integration modes

| Mode | Use when |
|---|---|
| **Standalone** (`HasKnet` trait / `KnetPaymentService`) | KNET is the only gateway, or you want KNET's own transaction table and flow. Unchanged from v7. |
| **cashier-core driver** (`Cashier\KnetProcessor`, new in v8) | KNET is one PSP among several. Deposits share the engine's transaction table, fee snapshot, sync and admin surface. See *Cashier Core Driver* below. |

### Architecture

The package follows SOLID principles with focused interfaces:

- `CreatesPayments` — payment creation and response handling
- `InquiresPayments` — transaction status inquiry
- `RefundsPayments` — full and partial refunds
- `TransactionRepository` — data access abstraction
- `EncryptsPayload` — encryption abstraction (wraps AES-128-CBC)

`KnetPaymentService` is a pure delegating facade that implements all three payment interfaces. Prefer depending on the narrower interface when only a subset of functionality is needed.

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
- `payWithKfast(float $amount, array $options = []): KnetTransaction` — KFAST faster checkout (auto-generates 8-digit token)
- `refund(KnetTransaction $transaction, ?float $amount = null): array` — full or partial refund
- `kfastToken(): string` — overridable 8-digit numeric KFAST token generator
- `knetTransactions(): HasMany` — relationship to all transactions

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

### Via Focused Interface

If you only need payment creation, type-hint the narrower interface:

```php
use Asciisd\Knet\Contracts\CreatesPayments;

public function checkout(CreatesPayments $payments)
{
    $transaction = $payments->createPayment(auth()->user(), 25.500);
    return redirect($transaction->url);
}
```

### With KFAST (Faster Checkout)

```php
// Auto-generates 8-digit token from user ID (e.g., user ID 42 → "00000042")
$transaction = $user->payWithKfast(25.500, [
    'udf1' => 'order_id',
]);
return redirect($transaction->url);

// Or with a custom token override
$transaction = $user->payWithKfast(25.500, [
    'udf3' => '99887766',
]);

// Or pass udf3 directly to pay()
$transaction = $user->pay(25.500, [
    'udf3' => '12345678',
]);
```

Override the token strategy by implementing `kfastToken()` on your model:

```php
class User extends Authenticatable
{
    use HasKnet;

    public function kfastToken(): string
    {
        return str_pad((string) $this->customer_number, 8, '0', STR_PAD_LEFT);
    }
}
```

KFAST token rules:
- Must be exactly 8 numeric digits (validated automatically by `PaymentRequest`)
- Must uniquely identify the customer
- KFAST must be enabled on the terminal by the acquirer bank

### Important Rules

- Amounts MUST use 3 decimal places (KWD format): `10.000`, `25.500`, `0.250`
- Currency code `414` is the ISO code for Kuwaiti Dinar
- Track IDs must be unique per transaction
- UDF fields (`udf1`–`udf5`) carry custom data through the full payment lifecycle
- The `$transaction->url` contains the full KNET gateway URL to redirect the user to

## Payment Flow

1. **Initiate:** Call `$user->pay()` or `KnetPaymentService::createPayment()` — returns a `KnetTransaction` with a `url`
2. **Redirect:** Send the user to `$transaction->url` (the KNET payment page)
3. **Callback:** KNET POSTs encrypted `trandata` to `/knet/response` — middleware decrypts once and passes payload via request attributes
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
| `KnetRefundSucceeded` | `KnetTransaction $transaction, KnetTransaction $refundTransaction, float $amount` |
| `KnetRefundFailed` | `KnetTransaction $transaction, KnetTransaction $refundTransaction, string $reason` |
| `KnetResponseReceived` | `array $payload` |
| `KnetResponseHandled` | `array $payload` |
| `KnetTransactionCreated` | `KnetTransaction $transaction` |
| `KnetTransactionUpdated` | `KnetTransaction $transaction` |
| `KnetTransactionHasErrors` | `KnetTransaction $transaction` |
| `KnetReceiptSeen` | `KnetTransaction $transaction` |

## Working with KnetTransaction

```php
use Asciisd\Knet\KnetTransaction;

// Check status
$transaction->isCaptured();   // true if result == 'CAPTURED'
$transaction->hasStatus();    // true if result is not empty
$transaction->isRefundable(); // true if captured and not yet refunded
$transaction->isRefunded();   // true if refunded

// Get amount
$transaction->rawAmount();       // float, e.g. 10.0
$transaction->formattedAmount(); // string, e.g. "10.000"

// Access relationship
$transaction->owner; // the User (or custom model) who made the payment
```

To find by track ID, use the repository:

```php
use Asciisd\Knet\Contracts\TransactionRepository;

$repository = app(TransactionRepository::class);
$transaction = $repository->findByTrackId('track-123');
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

For presentation (colors, images), use the presenter:

```php
$presenter = $status->presenter();
$presenter->styleColor(); // 'success-status', 'info-status', 'danger-status'
$presenter->textColor();  // 'successText', 'infoText', 'dangerText'
$presenter->bgColor();    // 'successBG', 'infoBG', 'dangerBG'
$presenter->imageUrl();   // URL to status image
$presenter->toArray();    // Full presentation array
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

#### Via Trait

```php
// Full refund
$result = $user->refund($transaction);

// Partial refund
$result = $user->refund($transaction, 5.000);
```

#### Via Service

```php
// Full refund
$result = $paymentService->refundPayment($transaction);

// Partial refund (5 KWD of a 10 KWD transaction)
$result = $paymentService->refundPayment($transaction, 5.000);
```

#### Refund Validation

The service automatically validates before processing:
- Transaction must be captured and not already refunded (`isRefundable()`)
- Refund amount must be greater than zero
- Refund amount must not exceed the original transaction amount

Throws `KnetException` on validation failure.

#### Refund Behavior

- On success: original transaction is marked `refunded = true`, `refunded_at` is set, `refund_amount` is recorded
- A separate `KnetTransaction` record is created for the refund (with `action = 2` and `original_transaction_id` linking to the original)
- `KnetRefundSucceeded` event is dispatched on success; `KnetRefundFailed` on failure
- Refund result is `CAPTURED` for success; any other value is a failure

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

> **Both flags are honored as of v8.** `KnetServiceProvider` never read
> `Knet::$registersRoutes` / `Knet::$runsMigrations` before — calling them on v7 and earlier had no
> effect at all. Migrations additionally only load `runningInConsole()`.

## Cashier Core Driver (v8)

`KnetServiceProvider::registerCashierDriver()` merges `['knet' => KnetProcessor::class]` into
`cashier-core.drivers` (a host-declared entry wins), so any connection declaring `driver => knet`
resolves through `ConnectionRegistry`.

```php
// config/cashier-core.php — credentials still live in config/knet.php
'connections' => [
    'knet' => ['driver' => 'knet'],
],
```

```php
use Asciisd\CashierCore\Services\PaymentService;

$result = app(PaymentService::class)->processPayment(
    customer: $user,                    // uses HasKnet AND implements CustomerContract
    paymentData: ['amount' => 10000],   // integer minor units — see the trap below
    connection: 'knet',
);

return redirect($result->getRedirectUrl());
```

### Core hooks implemented

| Hook | Behavior |
|---|---|
| `PreparesChargeData` | injects the payable Eloquent model as `paymentData['user']` (KNET's initiation needs the model, not an id) and pins `currency` to `KWD`. This is why the core `PaymentService` carries no KNET branch |
| `ProvidesWebhookTransactionId` | correlation id is `trackid` |

Supported features: `charge`, `refund`, `inquiry`. `capture()`, `authorize()`, `void()` throw
`BadMethodCallException`.

### ⚠️ Amount units

`KnetProcessor::charge()` treats `data['amount']` as **integer minor units** and divides by 100 to
get KWD; `KnetAdapter` multiplies `amt` back by 100 on the way out. Cashier-core transaction amounts
are decimal, so passing a decimal KWD figure straight to `processPayment()` on the `knet` connection
charges 1/100th of the intended amount. Verify the unit at every call site, and revisit this
semantic if a host ever feeds decimal KWD through the deposit flow.

### ⚠️ Results do not arrive through the cashier webhook route

`KnetProcessor::verifyWebhookSignature()` returns **false** by design. KNET posts its encrypted
result to the package's own `/knet/response` endpoint, verified by `VerifyKnetResponseSignature`
middleware — not to `POST api/webhooks/knet`. So a KNET deposit is driven by
`KnetPaymentSucceeded` / `KnetPaymentFailed` listeners, and the core's `WebhookProcessor` events
(`DepositSucceeded`, `FundsCredited`, …) do **not** fire for it. A host listening to both must not
double-credit.

`retrieve()` is the sync path: it looks the transaction up by `trackid` and runs
`inquireAndUpdateTransaction()`, so a generic "sync with provider" admin action works for KNET.

### UDF mapping at charge time

| Field | Value |
|---|---|
| `udf1` | `metadata.user_id` |
| `udf2` | `metadata.user_email` |
| `udf4` | `metadata.trading_account_login` (funding account) |
| `udf5` | description, sanitized to `[a-zA-Z0-9 -]` and truncated to 40 chars |

Empty values are filtered out. Remember UDF fields forbid `@` and `/` — the sanitizer strips them,
which is why an email must not be routed into `udf5`.

### Status map (`KnetAdapter::mapStatus()`)

| KNET result | cashier-core `PaymentStatus` |
|---|---|
| `SUCCESS`, `CAPTURED` | `Succeeded` |
| `FAILED`, `NOT CAPTURED`, `DECLINED`, `RESTRICTED`, `VOID`, `TIMEDOUT`, `ABANDONED` | `Failed` |
| `CANCELLED` | `Canceled` |
| `INITIATED` | `RequiresAction` |
| `PENDING`, `UNKNOWN`, *default* | `Pending` |

Accepts a `Asciisd\Knet\Enums\PaymentStatus` case or the raw string (uppercased).

### Refunds through the driver

`KnetProcessor::refund($trackId, $amountInMinorUnits)` resolves the `KnetTransaction` by `trackid`
and delegates to `KnetPaymentService::refundPayment()`; a null amount is a full refund. Success is
read from `status === 'success'` or `result === 'SUCCESS'` and mapped to `RefundStatus`.

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

## KNET Gateway Protocol (K-064)

This section documents the underlying KNET Payment Gateway protocol (doc K-064 v1.4) that the package abstracts.

### Gateway URLs

| Environment | Portal | Transaction Pipe (RAW) |
| --- | --- | --- |
| Test | `https://www.kpaytest.com.kw/kpg/merchant.htm` | `https://www.kpaytest.com.kw/kpg/tranPipe.htm?param=tranInit&` |
| Production | `https://www.kpay.com.kw/portal/merchant.htm` | `https://www.kpay.com.kw/kpg/tranPipe.htm?param=tranInit&` |

### Action Codes

| Action | Code | Description |
| --- | --- | --- |
| Purchase | `1` | Standard payment transaction |
| Refund (Credit) | `2` | Refund a captured transaction |
| Inquiry | `8` | Query transaction status |

### RAW Integration (used by this package)

The package uses RAW-based integration which requires:
- **Tran Portal ID** (`KNET_TRANSPORT_ID`) — unique terminal identifier
- **Tran Portal Password** (`KNET_TRANSPORT_PASSWORD`) — terminal password
- **Terminal Resource Key** (`KNET_RESOURCE_KEY`) — 16-char AES-128-CBC key for encrypting/decrypting `trandata`

RAW requests use `Content-type: application/xml` and send XML-formatted parameters to the `tranPipe.htm` endpoint.

### Payment Initialization Variables

| Variable | Type | Length | Required | Description |
| --- | --- | --- | --- | --- |
| Tran Portal ID | STRING | 255 | Y | Unique terminal ID |
| Tran Portal Password | STRING | 15 | Y | Terminal password |
| Terminal Resource Key | STRING | 16 | Y | AES encryption key |
| Action | STRING | 1 | Y | `1` (purchase), `2` (refund), `8` (inquiry) |
| Amount | STRING | 10 | Y | 3 decimal places for KWD: `15.750`, `10.000` |
| Currency Code | STRING | 3 | Y | `414` for KWD |
| Language | STRING | 3 | Y | `EN` or `AR` |
| Response URL | STRING | 255 | Y | URL for KNET to POST transaction response |
| Error URL | STRING | 255 | Y | URL for consumer redirect on error |
| Track ID | STRING | 40 | Y | Unique merchant-generated tracking ID (alphanumeric only) |
| Trans ID | STRING | 19 | Y (Refund/Inquiry) | Identifies the original transaction |
| UDF1–UDF4 | STRING | 255 | N | User-defined fields for custom data |
| UDF3 (KFAST) | STRING | 8 | N | 8-digit numeric customer token for KFAST faster checkout |
| UDF5 | STRING | 255 | N | **Purchase:** extra data, prefix with `"ptlf"` for bank reports (max 50 chars). **Inquiry:** identifier type — `"TrackID"`, `"PaymentID"`, `"TransID"`, or `"SeqNum"` |

### Transaction Response Variables

| Variable | Description |
| --- | --- |
| paymentid | Unique order ID from Payment Gateway |
| result | `CAPTURED`, `NOT CAPTURED`, `CANCELED`, `HOST TIMEOUT` |
| ref | Reference number from KNET |
| trackid | Merchant's original tracking ID |
| tranid | Transaction ID from Payment Gateway |
| postdate | Post date in `MMDD` format |
| auth | Authorization code from the bank |
| amt | Transaction amount (3 decimal places) |
| udf1–udf5 | User-defined fields echoed back |
| avr | Address verification response |
| authRespCode | Reason code for the transaction result |
| Error | Error code (if unsuccessful) |
| ErrorText | Error description (if unsuccessful) |

### Response Notification Contract

When KNET POSTs the response to the `responseURL`:
1. KNET sends an encrypted `trandata` parameter via URL-encoded POST
2. The merchant **must** decrypt the response, cross-verify with their database, and save both encrypted and plain text
3. The response page **must** output a single line: `REDIRECT=<Merchant Receipt URL>`
4. The response page must have **no HTML tags, no errors, no redirections** — only the `REDIRECT=` text
5. KNET reads this output and redirects the customer's browser to that URL

### Result Values

| Result | Meaning | Context |
| --- | --- | --- |
| `CAPTURED` | Transaction approved | Purchase, Inquiry, Refund |
| `NOT CAPTURED` | Transaction declined by bank | Purchase |
| `CANCELED` | Customer canceled on payment page | Purchase |
| `HOST TIMEOUT` | Bank did not respond in time | Purchase |
| `SUCCESS` | Transaction available and captured | Inquiry |
| `FAILURE` | Transaction failed | Inquiry |
| `SUSPECTED` | Transaction suspected | Inquiry |

### Inquiry Protocol

Inquiry uses action code `8`. The `udf5` parameter determines which identifier is used to locate the original transaction:

| Inquire by | transid value | udf5 value | amt | trackid |
| --- | --- | --- | --- | --- |
| Transaction ID | Original TransId | Empty or `"TransID"` | Original Amount | Original TrackId |
| Track ID | Original TrackId | `"TrackID"` | Original Amount | Original TrackId |
| Payment ID | Original PaymentId | `"PaymentID"` | Original Amount | Original TrackId |
| Reference ID | Original RefId | `"SeqNum"` | Original Amount | Original TrackId |

### Refund Protocol

Refund uses action code `2`. When refunding by Track ID:
- Set `transid` = original Track ID value
- Set `trackid` = original Track ID value
- Set `udf5` = `"TrackID"`
- Refund is successful **only** if result is `CAPTURED`; any other value is a failure

### KFAST (Faster Checkout)

KFAST allows registered cardholders to skip entering card details. To enable:
- Pass an 8-digit numeric token in `UDF3` that uniquely identifies the customer
- The merchant **must** ensure the correct token is passed for each customer
- KFAST must be enabled on the terminal by the acquirer bank
- OTP is required for initial card registration

### Validation Constraints

**Restricted characters:**

| Field | Forbidden Characters |
| --- | --- |
| UDF1–UDF5 | `@`, `/` |
| Track ID | `-`, `=`, `[`, `]`, `/`, `?`, `.` |

Track ID must be alphanumeric, max 40 characters, unique per transaction.

### Test Environment

- **Test URL:** `https://www.kpaytest.com.kw/kpg/merchant.htm`
- **Test Card:** Select "KNET Test Card [KNET1]" from the bank dropdown
- **CAPTURED result:** Set expiration date to `09/2021`
- **NOT CAPTURED result:** Use any other expiration date
- **PIN:** Any 4-digit numeric value works in the test environment

### Error Codes (Common)

Error codes follow the pattern `IPAY01xxxxx` (validation/processing) and `IPAY02xxxxx` (system/database). Key codes:

| Code | Description |
| --- | --- |
| IPAY0100001 | Missing error URL |
| IPAY0100003 | Missing response URL |
| IPAY0100005 | Missing tranportal ID |
| IPAY0100008 | Terminal not enabled |
| IPAY0100013 | Invalid transaction data |
| IPAY0100015 | Invalid tranportal password |
| IPAY0100017 | Inactive terminal |
| IPAY0100018 | Terminal password expired |
| IPAY0100020 | Invalid action type |
| IPAY0100023 | Missing amount |
| IPAY0100024 | Invalid amount |
| IPAY0100027 | Invalid track id |
| IPAY0100033 | Terminal action not enabled |
| IPAY0100036 | UDF Mismatched |
| IPAY0100042 | Transaction time limit exceeded |
| IPAY0100045 | Denied by Risk |
| IPAY0100048 | Cancelled |
| IPAY0100050 | Invalid terminal key |
| IPAY0100158 | Host timeout |
| IPAY0100176 | Decrypting transaction data failed |
| IPAY0100249 | Merchant response URL is down |
| IPAY0100264 | Signature validation failed |

Full error code list: see K-064 Integration Manual Section 10.

## When Adding New Features

- New services go in `src/Services/` and should implement the appropriate focused contract
- For encryption, inject `EncryptsPayload` — do not use `KPayClient` static methods directly
- For data access, inject `TransactionRepository` — do not use model static methods directly
- Configuration access should go through the injected `KnetConfig` singleton, not bare `config()` calls
- Verbose logging should be gated behind `$config->isDebugMode()`; only error-level logging should be unconditional
- New events go in `src/Events/` with `Dispatchable` and `SerializesModels` traits
- New exceptions go in `src/Exceptions/` extending `KnetException`
- All amounts must use `number_format($amount, 3, '.', '')` for 3-decimal KWD formatting
- **Never read or assert credentials while constructing a service.** They are validated on use, so a
  host that installs the package without configuring KNET still boots — and so cashier-core can
  resolve the `knet` driver in an app where KNET is declared but not live
- Anything under `src/Cashier/` must talk only to `Asciisd\CashierCore\*` contracts and this
  package's own classes — never to a host-namespaced class
- Changes to `KnetProcessor::charge()`, `KnetAdapter` amount handling, or the status map must be
  mirrored in the cashier-core driver tests; the minor-units convention is load-bearing on both sides
- A `KnetTransaction` row carries PII (`card_number`, `ip_address`). Anything that persists the row
  into a host's payment tables must run it through the engine's sanitizer first

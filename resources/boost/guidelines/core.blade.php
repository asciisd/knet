## Asciisd Knet — v8

This package provides an expressive, fluent interface to Kuwait's KNET payment gateway for Laravel applications. It handles payment initiation, encrypted callback processing, transaction inquiry, and refunds.

**Requires:** PHP ^8.3, Laravel ^11|^12|^13 (Laravel 10 dropped in v8), `asciisd/cashier-core ^2.0`.
`Knet::VERSION` is `8.0.0`.

### Two ways to use it

1. **Standalone** — the `HasKnet` trait / `KnetPaymentService`, unchanged from v7. Full control over
   the KNET flow, its own `knet_transactions` table, its own routes and events.
2. **As a cashier-core driver** (new in v8) — `src/Cashier/{KnetProcessor,KnetAdapter}` plug KNET
   into the `asciisd/cashier-core` v2 engine so KNET deposits share the app's transaction table,
   fee snapshot, sync and admin surface with every other PSP. See *Cashier Core Driver* below.

### Architecture

The package follows SOLID principles with focused interfaces:

- `CreatesPayments` — payment creation and response handling
- `InquiresPayments` — transaction status inquiry
- `RefundsPayments` — full and partial refunds
- `TransactionRepository` — data access abstraction
- `EncryptsPayload` — encryption abstraction (wraps AES-128-CBC)

`KnetPaymentService` is a pure delegating facade that implements all three payment interfaces. Prefer the narrower interface when only a subset of functionality is needed.

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
- KNET callbacks are encrypted with AES-128-CBC; the middleware decrypts once and passes the payload via request attributes.
- For encryption, inject `EncryptsPayload` — do not call `KPayClient` static methods directly (deprecated).
- For data access, inject `TransactionRepository` — do not use model static methods directly.
- Configuration access should go through the injected `KnetConfig` singleton, not bare `config()` calls.
- Verbose logging should be gated behind `$config->isDebugMode()`; only error-level logging should be unconditional.
- **KNET credentials are validated on use, not on construction.** Building `KnetPaymentService` (or resolving it as a cashier-core driver) must not read or assert credentials — a host that has KNET installed but not configured would otherwise fail to boot.
- **A `KnetTransaction` row carries PII**: `card_number` and `ip_address` among others. Any host persisting the row (commonly on failure) must run it through the payment engine's sanitizer/redactor first, and list `ip_address` + `card_number` in `cashier-core.security.redact_keys`.

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

// Access the user's transactions
$transactions = $user->knetTransactions;
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

Or type-hint the focused interface if you only need payment creation:

@verbatim
<code-snippet name="Using the focused CreatesPayments interface" lang="php">
use Asciisd\Knet\Contracts\CreatesPayments;

public function checkout(CreatesPayments $payments)
{
    $transaction = $payments->createPayment(auth()->user(), 25.500);
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

For presentation (colors, images), use the presenter:

@verbatim
<code-snippet name="Using PaymentStatusPresenter" lang="php">
$status = PaymentStatus::from($transaction->result);
$presenter = $status->presenter();

$presenter->styleColor(); // 'success-status', 'info-status', 'danger-status'
$presenter->textColor();  // 'successText', 'infoText', 'dangerText'
$presenter->bgColor();    // 'successBG', 'infoBG', 'dangerBG'
$presenter->toArray();    // Full presentation array
</code-snippet>
@endverbatim

### Working with Transactions

To find transactions, use the repository:

@verbatim
<code-snippet name="Using TransactionRepository" lang="php">
use Asciisd\Knet\Contracts\TransactionRepository;

public function show(TransactionRepository $repository, string $trackId)
{
    $transaction = $repository->findByTrackId($trackId);

    return view('payment.show', compact('transaction'));
}
</code-snippet>
@endverbatim

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

### Cashier Core Driver (v8)

`KnetServiceProvider` registers `knet` in `cashier-core.drivers`, so any connection declaring
`driver => knet` resolves to `Asciisd\Knet\Cashier\KnetProcessor` through `ConnectionRegistry`.

@verbatim
<code-snippet name="KNET as a cashier-core connection" lang="php">
// config/cashier-core.php
'connections' => [
    'knet' => ['driver' => 'knet'],   // credentials still come from config/knet.php
],

// Charging goes through the core engine like any other PSP
$result = app(\Asciisd\CashierCore\Services\PaymentService::class)->processPayment(
    customer: $user,                       // must use HasKnet AND implement CustomerContract
    paymentData: ['amount' => 10000],      // integer minor units — see the trap below
    connection: 'knet',
);

return redirect($result->getRedirectUrl());
</code-snippet>
@endverbatim

`KnetProcessor` implements two cashier-core hooks:

- **`PreparesChargeData`** — injects the payable Eloquent model as `paymentData['user']` (KNET's
  payment initiation needs the model itself, not an id) and pins `currency` to `KWD`. This is why
  the core `PaymentService` needs no KNET branch.
- **`ProvidesWebhookTransactionId`** — the correlation id is `trackid`.

Supported features: `charge`, `refund`, `inquiry`. `capture()`, `authorize()` and `void()` throw
`BadMethodCallException`. `verifyWebhookSignature()` returns **false** by design: KNET results
arrive on the package's own encrypted `/knet/response` callback (verified by
`VerifyKnetResponseSignature` middleware), not through the cashier-core webhook route — so KNET
deposits are driven by `KnetPaymentSucceeded` / `KnetPaymentFailed` listeners, not by the core's
`WebhookProcessor` events. A host listening to both must not double-credit.

> **⚠️ Amount units trap.** `KnetProcessor::charge()` treats `data['amount']` as **integer minor
> units** and divides by 100 to get KWD, and `KnetAdapter` multiplies `amt` back by 100. Cashier-core
> transaction amounts are decimal. Feeding a decimal KWD figure straight into `processPayment()` for
> the `knet` connection charges 1/100th of the intended amount. Verify the unit at every call site.

UDF mapping at charge time: `udf1` = user id, `udf2` = user email, `udf4` = funding account login,
`udf5` = the description, sanitized to alphanumerics/spaces/dashes and truncated to 40 chars.

Status map (`KnetAdapter::mapStatus()`):

| KNET result | PaymentStatus |
|---|---|
| `SUCCESS`, `CAPTURED` | `Succeeded` |
| `FAILED`, `NOT CAPTURED`, `DECLINED`, `RESTRICTED`, `VOID`, `TIMEDOUT`, `ABANDONED` | `Failed` |
| `CANCELLED` | `Canceled` |
| `INITIATED` | `RequiresAction` |
| `PENDING`, `UNKNOWN`, *default* | `Pending` |

### Customization

- Use `Knet::ignoreMigrations()` in a service provider to skip package migrations.
- Use `Knet::ignoreRoutes()` to disable automatic route registration.
- **Both flags are honored as of v8** — the provider read neither before, so calling them had no
  effect on v7 and earlier.
- Use `KnetTransaction::useCustomerModel(YourModel::class)` to change the billable model.
- Use UDF fields (`udf1` through `udf5`) to pass custom data through the payment flow.

### Artisan Commands

| Command | Purpose |
|---|---|
| `knet:install` | Full setup: publish config, run migrations, validate |
| `knet:check` | Validate credentials and configuration |
| `knet:publish` | Publish config (`--config`) and/or migrations (`--migrations`) |

### KNET Gateway Protocol Reference (K-064)

The underlying KNET Payment Gateway protocol (doc K-064 v1.4) that this package abstracts.

**Gateway URLs:**

| Environment | Portal | Transaction Pipe (RAW) |
|---|---|---|
| Test | `https://www.kpaytest.com.kw/kpg/merchant.htm` | `https://www.kpaytest.com.kw/kpg/tranPipe.htm?param=tranInit&` |
| Production | `https://www.kpay.com.kw/portal/merchant.htm` | `https://www.kpay.com.kw/kpg/tranPipe.htm?param=tranInit&` |

**Action Codes:** `1` = Purchase, `2` = Refund (Credit), `8` = Inquiry.

**RAW Integration:** The package uses RAW-based integration with `Content-type: application/xml`. Requires Tran Portal ID, Tran Portal Password, and a 16-char Terminal Resource Key for AES-128-CBC encryption of `trandata`.

**Response Notification Contract:** When KNET POSTs to the `responseURL`, the response page must output a single line `REDIRECT=<Merchant Receipt URL>` with no HTML tags, no errors, and no redirections. KNET reads this and redirects the customer's browser.

**Result Values:**

| Result | Meaning | Context |
|---|---|---|
| `CAPTURED` | Approved | Purchase, Inquiry, Refund |
| `NOT CAPTURED` | Declined by bank | Purchase |
| `CANCELED` | Customer canceled | Purchase |
| `HOST TIMEOUT` | Bank did not respond | Purchase |
| `SUCCESS` / `FAILURE` / `SUSPECTED` | Inquiry-specific results | Inquiry |

**Inquiry Protocol:** Uses action `8`. Set `udf5` to specify the identifier type: `"TrackID"`, `"PaymentID"`, `"TransID"`, or `"SeqNum"`. Set `transid` to the corresponding original value. Always include the original `amt` and `trackid`.

**Refund Protocol:** Uses action `2`. When refunding by Track ID, set both `transid` and `trackid` to the original Track ID, and `udf5` to `"TrackID"`. Refund is successful only when result is `CAPTURED`.

**KFAST (Faster Checkout):** Pass an 8-digit numeric customer token in `UDF3`. Must be enabled on the terminal by the acquirer bank. Merchant must ensure the correct token per customer.

**Validation Constraints:**

| Field | Forbidden Characters |
|---|---|
| UDF1–UDF5 | `@`, `/` |
| Track ID | `-`, `=`, `[`, `]`, `/`, `?`, `.` |

Track ID: alphanumeric only, max 40 characters, unique per transaction. Amounts are STRING with 3 decimal places (KWD).

**Test Environment:** Use test card "KNET Test Card [KNET1]". Expiry `09/2021` = CAPTURED, any other = NOT CAPTURED. Any 4-digit PIN.

**Common Error Codes:** `IPAY0100001` (missing error URL), `IPAY0100005` (missing tranportal ID), `IPAY0100008` (terminal not enabled), `IPAY0100013` (invalid transaction data), `IPAY0100015` (invalid password), `IPAY0100027` (invalid track id), `IPAY0100042` (time limit exceeded), `IPAY0100045` (denied by risk), `IPAY0100158` (host timeout), `IPAY0100176` (decryption failed), `IPAY0100249` (response URL is down).

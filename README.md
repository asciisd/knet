[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

# Knet Payment Integration for Laravel

A Laravel package for integrating with the new Knet payment portal.

> [!CAUTION]
> This package works with the new Knet payment portal and is not compatible with the old version. The receipt system has been removed to avoid conflicts with your application's existing system. Use the `KnetTransaction` model to implement your own receipt handling.

> [!NOTE]
> This package is currently being updated. Please avoid using in production until a stable version is released.

## Installation

1. Install via Composer:
```bash
composer require asciisd/knet
```

2. Install package resources:
```bash
php artisan knet:install
php artisan knet:publish
```

3. Run migrations:
```bash
php artisan migrate
```

## Configuration

1. Add environment variables to your `.env` file:
```dotenv
KENT_TRANSPORT_ID=your_transport_id
KENT_TRANSPORT_PASSWORD=your_password
KENT_RESOURCE_KEY=your_resource_key
KNET_DEBUG=true # Set to false in production
```

2. Add the `HasKnet` trait to your User model:
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Asciisd\Knet\HasKnet;

class User extends Authenticatable
{
    use HasKnet;
    // ...
}
```

## Basic Usage

### Creating a Payment

```php
use Asciisd\Knet\Services\KnetPaymentService;

class PaymentController 
{
    public function __construct(
        private KnetPaymentService $paymentService
    ) {}

    public function createPayment(Request $request)
    {
        try {
            $transaction = $this->paymentService->createPayment(
                user: auth()->user(),
                amount: $request->amount,
                options: [
                    'udf1' => 'Custom field 1',
                    'udf2' => 'Custom field 2',
                ]
            );

            return redirect($transaction->url);
            
        } catch (KnetException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }
}
```

### Handling Payment Response

The package automatically handles the payment response through its built-in controllers. You can listen for various events to handle the payment outcome:

```php
use Asciisd\Knet\Events\KnetPaymentSucceeded;
use Asciisd\Knet\Events\KnetPaymentFailed;

class PaymentEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        KnetPaymentSucceeded::class => [
            function (KnetPaymentSucceeded $event) {
                $transaction = $event->transaction;
                // Handle successful payment
            },
        ],
        KnetPaymentFailed::class => [
            function (KnetPaymentFailed $event) {
                $transaction = $event->transaction;
                $errorMessage = $event->errorMessage;
                // Handle failed payment
            },
        ],
    ];
}
```

### Available Events

- `KnetTransactionCreated`: Fired when a new transaction is created
- `KnetTransactionUpdated`: Fired when a transaction is updated
- `KnetResponseReceived`: Fired when response is received from Knet
- `KnetResponseHandled`: Fired after response is processed
- `KnetPaymentSucceeded`: Fired when payment is successful
- `KnetPaymentFailed`: Fired when payment fails

### Test Cards

| Card Number      | Expiry Date | PIN  | Status       |
|-----------------|-------------|------|--------------|
| 8888880000000001| 09/25       | 1234 | CAPTURED     |
| 8888880000000002| 05/25       | 1234 | NOT CAPTURED |

## Error Handling

The package throws `KnetException` for various error cases. Always wrap your payment code in try-catch blocks:

```php
try {
    $transaction = $paymentService->createPayment($user, $amount);
} catch (KnetException $e) {
    // Handle the error
    logger()->error('Payment failed: ' . $e->getMessage());
}
```

[ico-version]: https://img.shields.io/packagist/v/asciisd/knet.svg?style=flat
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat
[ico-downloads]: https://img.shields.io/packagist/dt/asciisd/knet.svg?style=flat

[link-packagist]: https://packagist.org/packages/asciisd/knet
[link-downloads]: https://packagist.org/packages/asciisd/knet

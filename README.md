[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

# Laravel KNET Payment Integration

A robust Laravel package for integrating KNET payment gateway services in your applications. This package provides a clean and elegant way to handle payment processing, refunds, and transaction management with KNET.

## Features

- 🔒 Secure payment processing
- 💳 Transaction management
- 🔄 Payment status inquiries
- ↩️ Refund processing
- 🎯 Event-driven architecture
- 📝 Detailed transaction logging
- 🛡️ Error handling
- 🔍 Transaction tracking

## Installation

You can install the package via composer:

```bash
composer require asciisd/knet
```

After installation, publish the configuration file:

```bash
php artisan knet:publish"
```

## Configuration

Configure your KNET credentials in your `.env` file:

```env
KNET_TRANSPORT_ID=your_transport_id
KNET_TRANSPORT_PASSWORD=your_transport_password
KNET_RESOURCE_KEY=your_resource_key

# URLs Optional
KNET_RESPONSE_URL=/knet/response
KNET_REDIRECT_URL=/dashboard
KNET_DEBUG=false
```

## Basic Usage

### Creating a Payment

```php
use Asciisd\Knet\Services\KnetPaymentService;

class PaymentController extends Controller
{
    public function createPayment(
        Request $request, 
        KnetPaymentService $paymentService
    ) {
        $transaction = $paymentService->createPayment(
            user: $request->user(),
            amount: 10.000,
            options: [
                'udf1' => 'custom_data_1',
                'udf2' => 'custom_data_2',
            ]
        );

        return redirect($transaction->url);
    }
}
```

### Manual Handling Payment Response

```php
public function handleResponse(
    Request $request, 
    KnetPaymentService $paymentService
) {
    $transaction = $paymentService->handlePaymentResponse($request->all());

    if ($transaction->paid) {
        return redirect()->route('payment.success');
    }

    return redirect()->route('payment.failed');
}
```

### Processing Refunds

```php
public function refund(
    KnetTransaction $transaction, 
    KnetPaymentService $paymentService
) {
    // Full refund
    $result = $paymentService->refundPayment($transaction);

    // Partial refund
    $result = $paymentService->refundPayment($transaction, 5.000);

    return $result;
}
```

### Checking Transaction Status

```php
public function checkStatus(
    KnetTransaction $transaction, 
    KnetPaymentService $paymentService
) {
    $updatedTransaction = $paymentService->inquireAndUpdateTransaction($transaction);
    return $updatedTransaction;
}
```

## Events

The package dispatches several events that you can listen to:

- `KnetResponseReceived`: Fired when a payment response is received
- `KnetResponsehandled`: Fired when a payment response is handled

### Event Listeners Example

```php
use Asciisd\Knet\Events\KnetResponseReceived;

class PaymentReceivedListener
{
    public function handle(KnetResponseReceived $event)
    {
        $transactionArray = $event->payload;
        // Handle payload
    }
}
```

## Transaction Model

The `KnetTransaction` model provides several helpful methods:

```php
$transaction->rawAmount(); // Get the raw amount
$transaction->isPaid(); // Check if transaction is paid
$transaction->isRefunded(); // Check if transaction is refunded
$transaction->isRefundable(); // Check if transaction can be refunded
```

## Error Handling

The package includes comprehensive error handling:

```php
try {
    $result = $paymentService->refundPayment($transaction);
} catch (\Exception $e) {
    Log::error('Refund failed', [
        'message' => $e->getMessage(),
        'transaction_id' => $transaction->id
    ]);
}
```

## Database Schema

The package includes migrations for the `knet_transactions` table with the following fields:

- `id`: Primary key
- `user_id`: Foreign key to users table
- `trackid`: KNET tracking ID
- `amt`: Transaction amount
- `paymentid`: KNET payment ID
- `tranid`: KNET transaction ID
- `ref`: Reference number
- `result`: Transaction result
- `auth`: Authorization code
- `avr`: AVR value
- `postdate`: Posting date
- `paid`: Payment status
- `error_text`: Error message if any
- `url`: Payment URL
- `livemode`: Production/Test mode flag
- Various UDF fields (udf1 to udf5)
- Refund-related fields
- Timestamps

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email security@asciisd.com instead of using the issue tracker.

## Credits

- [Your Name](https://github.com/yourusername)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/asciisd/knet.svg?style=flat
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat
[ico-downloads]: https://img.shields.io/packagist/dt/asciisd/knet.svg?style=flat

[link-packagist]: https://packagist.org/packages/asciisd/knet
[link-downloads]: https://packagist.org/packages/asciisd/knet

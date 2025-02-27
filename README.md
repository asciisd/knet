[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

# Knet

This package used to integrate with the new Knet payment portal

## Usage

Here are a few short examples of what you can do:

> [!CAUTION]
> Please note that this package is updated to work with the new Knet payment portal, and it's not compatible with the old
one, we removed the receipt system from the package that because it may conflict with your own system, so you can use
the `KnetTransaction` model details to show your own receipt.

> [!NOTE]
> The package now is under updating process, so please don't use it in production until we release the stable version.


#### First Step

add `HasKnet` trait to the User model

```php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Asciisd\Knet\HasKnet;

class User extends Authenticatable {
   use HasKnet;
}
```

#### Second Step

user `pay()` method

```php
// $transaction is the transaction instance from your own system, it could be anything
$payable = $transaction->user ?? auth()->user();

try {
    $payment = $payable->pay($transaction->amount, [
        'trackid' => $transaction->reference,
        'udf1' => $payable->name,
        'udf2' => $payable->email,
        'udf3' => $payable->phone,
    ]);
} catch (\Asciisd\Knet\Exceptions\KnetException $e) {
    logger()->error($e->getMessage());
} catch (\Asciisd\Knet\Exceptions\PaymentActionRequired $e) {
    // Update transaction with knet transaction id
    $transaction->forceFill([
        'transactional_id' => $e->payment->id,
        'transactional_type' => KnetTransaction::class,
    ])->save();

    return $e->payment->actionUrl();
}

return view('transactions.create')->withErrors('message', 'Payment failed');
```

> After finished the payment you will redirect to [/knet/response]()
> you can change that from config file to make your own handler

#### Another Example:

you can use `pay()` method inside controller like this

```php
try{
    $payment = request()->user()->pay(request()->amount, [
        'udf1' => request()->user()->name,
        'udf2' => request()->user()->email
    ]);
} catch(\Asciisd\Knet\Exceptions\PaymentActionRequired $exception) {
    // do whatever you want with this 
    $payment = $exception->payment;
} finally {
    // redirect user to payment url to complete the payment
    return $payment->actionUrl();
}
```

## Change Environment

you can change your environment from local to production in case you want to make sure that everything is working fine,
to do that change `.env` file like this

```dotenv
APP_ENV=local #or production

KENT_TRANSPORT_ID=
KENT_TRANSPORT_PASSWORD=
KENT_RESOURCE_KEY=
KNET_DEBUG=true #or false in production
``` 

## Installation

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

``` bash
$ composer require asciisd/knet
```

### Run install command:

this command will install `ServiceProvider`, `Configs` and `views`

``` bash
php artisan knet:install
```

### Run publish command:

this command will knet assets

```bash
php artisan knet:publish
```

After the migration has been published you can create the `knet_transactions` table by running the migrations:

``` bash
php artisan migrate
```

## KnetServiceProvider

This package provides a receipt system, but you should fill your identity details inside `KnetServiceProvider` =>
`$details` array
also you need to update your logo inside `vendor` => `knet` public assets

## Test cards

| Card Number      | Expiry Date | PIN  |    Status    |
|------------------|:-----------:|:-----|:------------:|
| 8888880000000001 |    09/25    | 1234 |   CAPTURED   |
| 8888880000000002 |    05/25    | 1234 | NOT CAPTURED |

## Events

You can add this code to `EventServiceProvider`

```
KnetTransactionCreated::class => [
    // this event hold the new transaction instance
    // you can get this transaction inside the listner by $event->transaction
];

KnetTransactionUpdated::class => [
    // this event hold the updated transaction instance
    // you can get this transaction inside the listner by $event->transaction
];

KnetResponseReceived::class => [
    // this event hold the knet response array()
    // you can get this payload inside the listner by $event->payload
];

KnetResponseHandled::class => [
    // this event hold the knet response array()
    // you can get this payload inside the listner by $event->payload
];

KnetReceiptSeen::class => [
    // this event hold the knet Payment as $payment
];
```

[ico-version]: https://img.shields.io/packagist/v/asciisd/knet.svg?style=flat

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat

[ico-status]: https://github.com/asciisd/knet/workflows/tests/badge.svg

[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/asciisd/knet.svg?style=flat

[ico-code-quality]: https://img.shields.io/scrutinizer/g/asciisd/knet.svg?style=flat

[ico-downloads]: https://img.shields.io/packagist/dt/asciisd/knet.svg?style=flat

[link-packagist]: https://packagist.org/packages/asciisd/knet

[link-actions]: https://github.com/asciisd/knet/actions

[link-scrutinizer]: https://scrutinizer-ci.com/g/asciisd/knet/code-structure

[link-code-quality]: https://scrutinizer-ci.com/g/asciisd/knet

[link-downloads]: https://packagist.org/packages/asciisd/knet

[link-author]: https://github.com/asciisd

[link-contributors]: ../../contributors

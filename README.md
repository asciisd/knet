# Knet

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

This package used to integrate with the new Knet payment portal

## Using this package

Here are a few short examples of what you can do:

####First Step
add `HasKnet` trait to the User model
```php
<?php

namespace App;

use Asciisd\Knet\HasKnet;

class User extends Authenticatable {
   use HasKnet;
}
```

####Second Step
user `pay()` method

```php 
$payment = $user->pay(10);
$payment->url; // this will return payment link

return redirect($payment->url);
```

After finished the payment you will redirected to [/knet/response]()
you can change that from config file to make your own handler

####Another Example:
you can use `pay()` method inside controller like this
```php
public function update(Request $request, $id)
    {
        $payment = $request->user()->pay($request->amount, [
            'udf1' => $request->user()->name,
            'udf2' => $request->user()->email,
        ]);
    }
``` 

## Change Environment
you can change your environment from local to production in case you want to make sure that everything is working fine, to do that change `.env` file like this

```dotenv
APP_ENV=local // or production

KENT_TRANSPORT_ID=
KENT_TRANSPORT_PASSWORD=
KENT_RESOURCE_KEY=
KNET_DEBUG=true //or false in production
``` 

## Installation

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

``` bash
$ composer require asciisd/knet
```

You can publish the migration with:

``` bash
php artisan vendor:publish --provider="Asciisd\Knet\Providers\KnetServiceProvider" --tag="knet-migrations"
```

After the migration has been published you can create the knet_transactions table by running the migrations:
``` bash
php artisan migrate
```

You can publish the config-file with:

``` bash
php artisan vendor:publish --provider="Asciisd\Knet\Providers\KnetServiceProvider" --tag="knet-config"
```

## Test cards
    Card Number	Expiry Date	PIN	Status
    
    8888880000000001	09/21	1234	CAPTURED
    8888880000000002	05/21	1234	NOT CAPTURED


## Events

You can add this code to EventServiceProvider
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
```

[ico-version]: https://img.shields.io/packagist/v/asciisd/knet.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/asciisd/knet/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/asciisd/knet.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/asciisd/knet.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/asciisd/knet.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/asciisd/knet
[link-travis]: https://travis-ci.org/asciisd/knet
[link-scrutinizer]: https://scrutinizer-ci.com/g/asciisd/knet/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/asciisd/knet
[link-downloads]: https://packagist.org/packages/asciisd/knet
[link-author]: https://github.com/asciisd
[link-contributors]: ../../contributors

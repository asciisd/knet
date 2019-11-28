# knet

This package used to integrate with the new Knet payment portal

## Using this package

Here are a few short examples of what you can do:

```php 
$payment = $user->pay(10);
$payment->url; // this will return payment link

return redirect($payment->url);
```

After finished the payment you will redirected to [/knet/response]()
you can change that from config file to make your own handler

## Installation

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

``` bash
$ composer require asciisd/knet
```

You can publish the migration with:

``` bash
php artisan vendor:publish --provider="Asciisd\Knet\KnetServiceProvider" --tag="migrations"
```

After the migration has been published you can create the knet_transactions table by running the migrations:
``` bash
php artisan migrate
```

You can publish the config-file with:

``` bash
php artisan vendor:publish --provider="Asciisd\Knet\KnetServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php 

return [

    /*
    |--------------------------------------------------------------------------
    | KNet production and development urls
    |--------------------------------------------------------------------------
    |
    | The KNet production url and development url give you access to Knet's
    | API. The "production" url is typically used when interacting with
    | production Api while the "development" url accesses testing API endpoints.
    |
    */
    'production_url' => env('KENT_PRODUCTION_URL', 'https://kpay.com.kw/kpg/PaymentHTTP.htm'),
    'development_url' => env('KENT_PRODUCTION_URL', 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm'),

    /*
    |--------------------------------------------------------------------------
    | Knet Credentials
    |--------------------------------------------------------------------------
    |
    | TranPortal Identification Number: The Payment Gateway Bank administrator
    | issues the TranPortal ID to identify the merchant and terminal for transaction
    | processing.
    |
    | TranPortal Password: The Payment Gateway Bank administrator issues the
    | TranPortal password to authenticate the merchant and terminal. Merchant data
    | will be encrypted and password securely hidden as long as the merchant is issuing
    | an https post for transmitting the data to Payment Gateway.
    |
    */
    'transport' => [
        'id' => env('KENT_TRANSPORT_ID'),
        'password' => env('KENT_TRANSPORT_PASSWORD'),
    ],

    'resource_key' => env('KENT_RESOURCE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Knet Response url
    |--------------------------------------------------------------------------
    |
    | The merchant URL where Payment Gateway send the authorization response
    |
    */
    'response_url' => env('KENT_RESPONSE_URL') ?? url('/knet/response'),

    /*
    |--------------------------------------------------------------------------
    | Knet Error url
    |--------------------------------------------------------------------------
    |
    | The merchant URL where Payment Gateway send the response in case any
    | error while processing the transaction.
    |
    */
    'error_url' => env('KENT_ERROR_URL') ?? url('/knet/error'),
    'success_url' => env('KENT_SUCCESS_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Action Code
    |--------------------------------------------------------------------------
    |
    | Transaction Action Type, "1" for Purchase.
    | Transaction Action Type, "2" for Refund.
    | Transaction Action Type, "3" for Void.
    | Transaction Action Type, "8" for Inquiry.
    |
    */
    'action_code' => env('KENT_ACTION_CODE', 1),

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | The language in which Payment Page has to be presented.
    |
    | Supported languages: 'AR', 'EN'
    |
    */
    'language' => env('KENT_LANGUAGE', 'EN'),

    /*
    |--------------------------------------------------------------------------
    | Knet Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Knet's views, such as the payment
    | verification screen, will be available from. You're free to tweak
    | this path according to your preferences and application design.
    |
    */
    'path' => env('KNET_PATH', 'knet'),

    /*
    |--------------------------------------------------------------------------
    | Knet Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the HasKnet trait
    | provided by Knet. It will serve as the primary model you use while
    | interacting with Knet related methods, and so on.
    |
    */
    'model' => env('KNET_MODEL', App\User::class),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when generating charges
    | from your application. Of course, you are welcome to use any of the
    | various world currencies that are currently supported via Knet.
    |
    */
    'currency' => env('KENT_CURRENCY', 414),
    'decimals' => '3',

    /*
    |--------------------------------------------------------------------------
    | Invoice Paper Size
    |--------------------------------------------------------------------------
    |
    | This option is the default paper size for all invoices generated using
    | Knet. You are free to customize this settings based on the usual
    | paper size used by the customers using your Laravel applications.
    |
    | Supported sizes: 'letter', 'legal', 'A4'
    |
    */
    'paper' => env('KNET_PAPER', 'letter'),
];

```

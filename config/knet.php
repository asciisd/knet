<?php

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
    'development_url' => env('KENT_DEVELOPMENT_URL', 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm'),

    'production_inquiry_url' => env('KENT_PRODUCTION_INQUIRY_URL', 'https://www.kpay.com.kw/kpg/tranPipe.htm'),
    'development_inquiry_url' => env('KENT_DEVELOPMENT_INQUIRY_URL', 'https://www.kpaytest.com.kw/kpg/tranPipe.htm'),
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
    | Knet Response url
    |--------------------------------------------------------------------------
    |
    | The merchant URL where Payment Gateway send the authorization response
    |
    */
    'response_url' => '/knet/response',

    /*
    |--------------------------------------------------------------------------
    | Knet Error url
    |--------------------------------------------------------------------------
    |
    | The merchant URL where Payment Gateway send the response in case any
    | error while processing the transaction.
    |
    */
    'error_url' => '/knet/error',
    'redirect_url' => env('KENT_REDIRECT_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale in which your money values are formatted in
    | for display. To utilize other locales besides the default en locale
    | verify you have the "intl" PHP extension installed on the system.
    |
    */

    'currency_locale' => env('KNET_CURRENCY_LOCALE', 'en'),

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
];

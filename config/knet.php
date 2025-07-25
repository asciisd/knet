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
    'production_url' => env('KNET_PRODUCTION_URL', 'https://kpay.com.kw/kpg/PaymentHTTP.htm'),
    'development_url' => env('KNET_DEVELOPMENT_URL', 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm'),

    'production_inquiry_url' => env('KNET_PRODUCTION_INQUIRY_URL', 'https://www.kpay.com.kw/kpg/tranPipe.htm'),
    'development_inquiry_url' => env('KNET_DEVELOPMENT_INQUIRY_URL', 'https://www.kpaytest.com.kw/kpg/tranPipe.htm'),
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
        'id' => env('KNET_TRANSPORT_ID'),
        'password' => env('KNET_TRANSPORT_PASSWORD'),
    ],

    'resource_key' => env('KNET_RESOURCE_KEY'),

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
    'currency' => env('KNET_CURRENCY', 414),
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
    'redirect_url' => env('KNET_REDIRECT_URL', '/'),

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
    'action_code' => env('KNET_ACTION_CODE', 1),

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
    'language' => env('KNET_LANGUAGE', 'EN'),

    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    | Sometimes you may need to use test credentials in production, or vice versa.
    |
    | So you can change this to true to use the development url in production.
    |
    */
    'debug' => env('KNET_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Debugging Configuration
    |--------------------------------------------------------------------------
    |
    | These options control detailed logging and debugging features for
    | troubleshooting KNet integration issues. Enable these only when
    | debugging specific problems as they can generate significant log data.
    |
    */

    // Enable detailed logging of hex conversion processes
    'debug_hex_conversion' => env('KNET_DEBUG_HEX_CONVERSION', false),

    // Enable logging of raw response data from KNet gateway
    'debug_response_data' => env('KNET_DEBUG_RESPONSE_DATA', false),

    // Enable detailed transaction processing logs
    'debug_transactions' => env('KNET_DEBUG_TRANSACTIONS', false),

    // Enable logging of encryption/decryption operations
    'debug_encryption' => env('KNET_DEBUG_ENCRYPTION', false),

    // Log validation failures with detailed context
    'debug_validation_failures' => env('KNET_DEBUG_VALIDATION_FAILURES', true),

    // Maximum length of hex data to log (prevents excessive log sizes)
    'debug_hex_preview_length' => env('KNET_DEBUG_HEX_PREVIEW_LENGTH', 200),

    // Enable automatic hex data analysis on errors
    'debug_auto_hex_analysis' => env('KNET_DEBUG_AUTO_HEX_ANALYSIS', false),

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control how errors are handled and reported, particularly
    | for hex validation and response processing failures.
    |
    */

    // Retry failed hex conversions with data cleanup
    'retry_hex_conversion' => env('KNET_RETRY_HEX_CONVERSION', true),

    // Maximum number of retry attempts for hex conversion
    'max_hex_retry_attempts' => env('KNET_MAX_HEX_RETRY_ATTEMPTS', 2),

    // Store failed hex data for later analysis
    'store_failed_hex_data' => env('KNET_STORE_FAILED_HEX_DATA', false),

    // Fallback behavior when hex validation fails
    // Options: 'strict' (throw exception), 'lenient' (log and continue), 'fallback' (attempt recovery)
    'hex_validation_mode' => env('KNET_HEX_VALIDATION_MODE', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | Conversion currency
    |--------------------------------------------------------------------------
    |
    | Sometimes you may need to use invoice, but you want to put another currency.
    |
    | as converted currency with conversion rate, so you can use any currency and
    | use the dependency injection to change the rate depends on api you use
    |
    */
    'conversion_currency' => env('KNET_CONVERSION_CURRENCY', 'KWD'),
    'conversion_rate' => env('KNET_CONVERSION_RATE', 1),
    'conversion_bank' => env('KNET_CONVERSION_BANK', 'KFH'),
];

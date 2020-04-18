<?php


namespace Asciisd\Knet\Traits;


use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

trait ManagesAppDetails
{
    /**
     * The application / product details.
     *
     * @var array
     */
    public static $details = [];

    /**
     * The e-mail addresses of all of the application's developers.
     *
     * @var array
     */
    public static $developers = [];

    /**
     * Define the application information.
     *
     * @param array $details
     * @return void
     */
    public static function details(array $details)
    {
        static::$details = $details;
    }

    /**
     * Get the product name from the application information.
     *
     * @return string
     */
    public static function product()
    {
        return static::$details['product'];
    }

    /**
     * Get the invoice meta information, such as product, etc.
     *
     * @return array
     */
    public static function generateInvoicesWith()
    {
        return array_merge([
            'vendor' => '',
            'product' => '',
            'street' => '',
            'location' => '',
            'phone' => '',
        ], static::$details);
    }

    /**
     * Get the invoice data payload for the given billable entity.
     *
     * @param mixed $billable
     * @return array
     */
    public static function invoiceDataFor($billable)
    {
        return array_merge([
            'vendor' => 'Vendor',
            'product' => 'Product'
        ], static::generateInvoicesWith());
    }

    /**
     * Determine if the given e-mail address belongs to a developer.
     *
     * @param string $email
     * @return bool
     */
    public static function developer($email)
    {
        if (in_array($email, static::$developers)) {
            return true;
        }

        foreach (static::$developers as $developer) {
            if (Str::is($developer, $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the e-mail addresses that are registered to developers.
     *
     * @param array $developers
     * @return void
     */
    public static function developers(array $developers)
    {
        static::$developers = $developers;
    }
}

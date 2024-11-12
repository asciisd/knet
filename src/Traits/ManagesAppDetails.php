<?php namespace Asciisd\Knet\Traits;

use Illuminate\Support\Str;

trait ManagesAppDetails
{
    /**
     * The application / product details.
     */
    public static array $details = [];

    /**
     * The e-mail addresses of all the application's developers.
     */
    public static array $developers = [];

    /**
     * Define the application information.
     */
    public static function details(array $details): void
    {
        static::$details = $details;
    }

    /**
     * Get the product name from the application information.
     */
    public static function product(): string
    {
        return static::$details['product'];
    }

    /**
     * Get the invoice data payload for the given billable entity.
     */
    public static function invoiceDataFor($billable): array
    {
        return array_merge([
            'vendor'  => 'Vendor',
            'product' => 'Product'
        ], static::generateInvoicesWith());
    }

    /**
     * Get the invoice meta information, such as product, etc.
     */
    public static function generateInvoicesWith(): array
    {
        return array_merge([
            'vendor'   => '',
            'product'  => '',
            'street'   => '',
            'location' => '',
            'phone'    => '',
        ], static::$details);
    }

    /**
     * Determine if the given e-mail address belongs to a developer.
     */
    public static function developer($email): bool
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
     */
    public static function developers(array $developers): void
    {
        static::$developers = $developers;
    }
}

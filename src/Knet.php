<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Traits\ManagesAppDetails;
use Asciisd\Knet\Traits\ManagesSupportOptions;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class Knet
{
    use ManagesAppDetails;
    use ManagesSupportOptions;

    /**
     * The Knet library version.
     *
     * @var string
     */
    const VERSION = '2.1.0';

    /**
     * The KPay API version.
     *
     * @var string
     */
    const KPAY_VERSION = 'v2';

    /**
     * The custom currency formatter.
     *
     * @var callable
     */
    protected static $formatCurrencyUsing;

    /**
     * Indicates if Knet migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Indicates if Knet routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * Configure Knet to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Configure Knet to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param int $amount
     * @param string|null $currency
     * @return string
     */
    public static function formatAmount($amount, $currency = null)
    {
        if (static::$formatCurrencyUsing) {
            return call_user_func(static::$formatCurrencyUsing, $amount, $currency);
        }

        $money = new Money($amount, new Currency(strtoupper($currency ?? config('knet.currency'))));

        $numberFormatter = new NumberFormatter(config('knet.currency_locale'), NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    public static function receipt($paymentid)
    {
        return  url('/knet/receipt?paymentid=' . $paymentid);
    }
}

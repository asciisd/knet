<?php

namespace Asciisd\Knet;

use Money\Money;
use Money\Currency;
use NumberFormatter;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Asciisd\Knet\Traits\ManagesAppDetails;
use Asciisd\Knet\Traits\ManagesSupportOptions;

class Knet
{
    use ManagesAppDetails;
    use ManagesSupportOptions;

    /**
     * The Knet library version.
     */
    const VERSION = '2.6.0';

    /**
     * The KPay API version.
     */
    const KPAY_VERSION = 'v2';

    /**
     * The custom currency formatter.
     */
    protected static $formatCurrencyUsing;

    /**
     * Indicates if Knet migrations will be run.
     */
    public static $runsMigrations = true;

    /**
     * Indicates if Knet routes will be registered.
     */
    public static $registersRoutes = true;

    /**
     * Configure Knet to not register its migrations.
     */
    public static function ignoreMigrations(): self
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Configure Knet to not register its routes.
     */
    public static function ignoreRoutes(): self
    {
        static::$registersRoutes = false;

        return new static;
    }

    /**
     * Format the given amount into a displayable currency.
     */
    public static function formatAmount(int $amount, string $currency = null): string
    {
        if (static::$formatCurrencyUsing) {
            return call_user_func(static::$formatCurrencyUsing, $amount, $currency);
        }

        $money = new Money($amount, new Currency(strtoupper($currency ?? config('knet.currency'))));

        $numberFormatter = new NumberFormatter(config('knet.currency_locale'), NumberFormatter::CURRENCY);
        $moneyFormatter  = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    public static function receipt($paymentid)
    {
        return url('/knet/receipt?paymentid='.$paymentid);
    }
}

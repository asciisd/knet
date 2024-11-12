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
     */
    const string VERSION = '3.0.0';

    /**
     * The KPay API version.
     */
    const string KPAY_VERSION = 'v2';

    /**
     * The custom currency formatter.
     */
    protected static $formatCurrencyUsing;

    /**
     * Indicates if Knet migrations will be run.
     */
    public static bool $runsMigrations = true;

    /**
     * Indicates if Knet routes will be registered.
     */
    public static bool $registersRoutes = true;

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
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    public static function receipt($paymentid)
    {
        return url('/knet/receipt?paymentid='.$paymentid);
    }
}

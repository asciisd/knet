<?php

namespace Asciisd\Knet;

class Knet
{
    /**
     * The Knet library version.
     */
    const string VERSION = '6.0.0';

    /**
     * The KPay API version.
     */
    const string KPAY_VERSION = 'v2';

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
}

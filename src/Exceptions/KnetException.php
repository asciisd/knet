<?php

namespace Asciisd\Knet\Exceptions;

use Exception;

class KnetException extends Exception
{
    /**
     * Create a new PaymentFailure instance.
     *
     * @return self
     */
    public static function unauthorizedReferer()
    {
        return new self(
            __('UNAUTHORIZED_REFERER')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     *
     * @return self
     */
    public static function missingResourceKey()
    {
        return new self(
            __('MISSING_RESOURCE_KEY')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     *
     * @return self
     */
    public static function missingAmount()
    {
        return new self(
            __('MISSING_AMOUNT')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     *
     * @return self
     */
    public static function missingTrackId()
    {
        return new self(
            __('MISSING_TRACK_ID')
        );
    }
}

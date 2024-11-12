<?php

namespace Asciisd\Knet\Exceptions;

use Exception;

class KnetException extends Exception
{
    /**
     * Create a new PaymentFailure instance.
     */
    public static function unauthorizedReferer(): KnetException
    {
        return new self(
            __('UNAUTHORIZED_REFERER')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     */
    public static function missingResourceKey(): KnetException
    {
        return new self(
            __('MISSING_RESOURCE_KEY')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     */
    public static function missingAmount(): KnetException
    {
        return new self(
            __('MISSING_AMOUNT')
        );
    }

    /**
     * Create a new PaymentFailure instance.
     */
    public static function missingTrackId(): KnetException
    {
        return new self(
            __('MISSING_TRACK_ID')
        );
    }
}

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
            'The request attempt failed because of an invalid request referer.'
        );
    }
}

<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\SignatureVerificationException;

abstract class KnetResponseSignature
{
    public static function verifyHeader($payload, $header, $trackid)
    {
        if (KnetTransaction::findByTrackId($trackid) == null) {
            throw SignatureVerificationException::factory(
                "No trackid found matching the expected",
                $payload,
                $header
            );
        }

        return true;
    }
}

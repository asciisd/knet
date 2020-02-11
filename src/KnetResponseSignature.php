<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\SignatureVerificationException;

abstract class KnetResponseSignature
{
    public static function verifyHeader($payload, $header, $trackid)
    {
        if (KnetTransaction::findByTrackId($trackid) == null) {
            throw SignatureVerificationException::factory(
                __('NO_TRACK_ID_MATCH'),
                $payload,
                $header
            );
        }

        return true;
    }
}

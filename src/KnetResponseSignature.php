<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\SignatureVerificationException;

abstract class KnetResponseSignature
{
    const EXPECTED_REFERER = 'https://kpaytest.com.kw/kpg/paymentrouter.htm';

    public static function verifyHeader($payload, $header, $trackid)
    {
        $referer = $header['referer'][0];

        if (empty($referer)) {
            throw SignatureVerificationException::factory(
                "No referer found with expected scheme",
                $payload,
                $header
            );
        }

        if ($referer != self::EXPECTED_REFERER) {
            throw SignatureVerificationException::factory(
                "No referer found matching the expected referer for payload",
                $payload,
                $header
            );
        }

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

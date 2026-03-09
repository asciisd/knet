<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Contracts\EncryptsPayload;

class KPayEncryption implements EncryptsPayload
{
    public function encrypt(string $data, string $key): string
    {
        return KPayClient::encryptAES($data, $key);
    }

    public function decrypt(string $data, string $key): string
    {
        return KPayClient::decryptAES($data, $key);
    }
}

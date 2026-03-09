<?php

namespace Asciisd\Knet\Contracts;

interface EncryptsPayload
{
    public function encrypt(string $data, string $key): string;

    public function decrypt(string $data, string $key): string;
}

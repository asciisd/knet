<?php

namespace Asciisd\Knet;

class KPayClient
{
    public function decryptAES($code, $key)
    {
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);
        $iv = $key;
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);

        return $this->pkcs5_unpad($decrypted);
    }

    public function hex2ByteArray($hexString)
    {
        $string = hex2bin($hexString);

        return unpack('C*', $string);
    }

    public function byteArray2String($byteArray)
    {
        $chars = array_map('chr', $byteArray);

        return implode($chars);
    }

    public function pkcs5_unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return '';
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return '';
        }

        return substr($text, 0, -1 * $pad);
    }

    public function encryptAES($str, $key)
    {
        $str = $this->pkcs5_pad($str);
        $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = $this->byteArray2Hex($encrypted);
        $encrypted = urlencode($encrypted);

        return $encrypted;
    }

    public function pkcs5_pad($text)
    {
        $blockSize = 16;
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text . str_repeat(chr($pad), $pad);
    }

    public function byteArray2Hex($byteArray)
    {
        $chars = array_map('chr', $byteArray);
        $bin = implode($chars);

        return bin2hex($bin);
    }
}

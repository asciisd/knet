<?php

namespace Asciisd\Knet;

class KPayClient
{
    public static function decryptAES($code, $key): string
    {
        $code = self::hex2ByteArray(trim($code));
        $code = self::byteArray2String($code);
        $iv = $key;
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);

        return self::pkcs5_unpad($decrypted);
    }

    public static function hex2ByteArray($hexString): false|array
    {
        $string = hex2bin($hexString);

        return unpack('C*', $string);
    }

    public static function byteArray2String($byteArray): string
    {
        $chars = array_map('chr', $byteArray);

        return implode($chars);
    }

    public static function pkcs5_unpad($text): string
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

    public static function encryptAES($str, $key): string
    {
        $str = self::pkcs5_pad($str);
        $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = self::byteArray2Hex($encrypted);
        $encrypted = urlencode($encrypted);

        return $encrypted;
    }

    public static function pkcs5_pad($text): string
    {
        $blockSize = 16;
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text.str_repeat(chr($pad), $pad);
    }

    public static function byteArray2Hex($byteArray): string
    {
        $chars = array_map('chr', $byteArray);
        $bin = implode($chars);

        return bin2hex($bin);
    }
}

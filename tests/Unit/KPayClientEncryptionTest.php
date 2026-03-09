<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Tests\TestCase;

class KPayClientEncryptionTest extends TestCase
{
    private const TEST_KEY = '0123456789abcdef'; // 16-byte key for AES-128

    public function test_encrypt_decrypt_round_trip()
    {
        $original = 'paymentid=100&result=CAPTURED&trackid=abc123';

        $encrypted = KPayClient::encryptAES($original, self::TEST_KEY);
        $decrypted = KPayClient::decryptAES(urldecode($encrypted), self::TEST_KEY);

        $this->assertEquals($original, $decrypted);
    }

    public function test_encrypt_produces_url_encoded_hex()
    {
        $encrypted = KPayClient::encryptAES('test=value', self::TEST_KEY);

        $decoded = urldecode($encrypted);
        $this->assertTrue(ctype_xdigit($decoded), 'Encrypted output should be valid hex');
    }

    public function test_encrypt_decrypt_with_unicode_characters()
    {
        $original = 'udf1=متجر&result=SUCCESS';

        $encrypted = KPayClient::encryptAES($original, self::TEST_KEY);
        $decrypted = KPayClient::decryptAES(urldecode($encrypted), self::TEST_KEY);

        $this->assertEquals($original, $decrypted);
    }

    public function test_encrypt_decrypt_with_long_payload()
    {
        $original = http_build_query([
            'paymentid' => 'PAY123456789',
            'result' => 'CAPTURED',
            'trackid' => 'TRACK_LONG_ID_123456',
            'auth' => 'AUTH123',
            'ref' => 'REF456789',
            'tranid' => 'TRAN987654',
            'amt' => '1500.000',
            'udf1' => 'order_999',
            'udf2' => 'metadata_here',
            'udf3' => 'extra_data_field',
            'udf4' => 'more_data',
            'udf5' => 'final_field',
        ]);

        $encrypted = KPayClient::encryptAES($original, self::TEST_KEY);
        $decrypted = KPayClient::decryptAES(urldecode($encrypted), self::TEST_KEY);

        $this->assertEquals($original, $decrypted);
    }

    public function test_pkcs5_pad_adds_correct_padding()
    {
        $padded = KPayClient::pkcs5_pad('hello');
        $this->assertEquals(16, strlen($padded));

        $padded = KPayClient::pkcs5_pad(str_repeat('a', 16));
        $this->assertEquals(32, strlen($padded));
    }

    public function test_pkcs5_unpad_removes_correct_padding()
    {
        $original = 'hello';
        $padded = KPayClient::pkcs5_pad($original);
        $unpadded = KPayClient::pkcs5_unpad($padded);

        $this->assertEquals($original, $unpadded);
    }

    public function test_pkcs5_pad_unpad_round_trip()
    {
        $testStrings = ['', 'a', 'hello world', str_repeat('x', 15), str_repeat('y', 16), str_repeat('z', 31)];

        foreach ($testStrings as $str) {
            $padded = KPayClient::pkcs5_pad($str);
            $this->assertEquals(0, strlen($padded) % 16, "Padded length must be multiple of 16 for '$str'");

            if (strlen($str) > 0) {
                $unpadded = KPayClient::pkcs5_unpad($padded);
                $this->assertEquals($str, $unpadded, "Round-trip failed for '$str'");
            }
        }
    }

    public function test_byte_array_to_string_and_back()
    {
        $original = 'Hello KNet';
        $byteArray = unpack('C*', $original);
        $string = KPayClient::byteArray2String($byteArray);

        $this->assertEquals($original, $string);
    }

    public function test_byte_array_to_hex_and_back()
    {
        $original = [0x48, 0x65, 0x6C, 0x6C, 0x6F]; // "Hello"
        $hex = KPayClient::byteArray2Hex($original);

        $this->assertEquals('48656c6c6f', $hex);

        $bytes = KPayClient::hex2ByteArray(str_pad($hex, 32, '0'));
        $this->assertIsArray($bytes);
    }

    public function test_different_keys_produce_different_ciphertext()
    {
        $payload = 'result=CAPTURED&trackid=test1';
        $key1 = '0123456789abcdef';
        $key2 = 'fedcba9876543210';

        $encrypted1 = KPayClient::encryptAES($payload, $key1);
        $encrypted2 = KPayClient::encryptAES($payload, $key2);

        $this->assertNotEquals($encrypted1, $encrypted2);
    }
}

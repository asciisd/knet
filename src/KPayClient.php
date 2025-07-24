<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\InvalidHexDataException;

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

    /**
     * Enhanced hex2ByteArray with robust validation and error handling
     */
    public static function hex2ByteArray($hexString): array
    {
        // Validate input is not null or empty
        if (empty($hexString) || $hexString === null) {
            $debugInfo = self::generateDebugInfo($hexString, 'Empty or null input');
            throw InvalidHexDataException::emptyData($debugInfo);
        }

        // Trim whitespace and convert to string if needed
        $hexString = trim((string) $hexString);

        // Re-check after trimming
        if (empty($hexString)) {
            $debugInfo = self::generateDebugInfo($hexString, 'Empty after trimming');
            throw InvalidHexDataException::emptyData($debugInfo);
        }

        // Check for odd length (hex strings must have even number of characters)
        if (strlen($hexString) % 2 !== 0) {
            $debugInfo = self::generateDebugInfo($hexString, 'Odd length detected');
            throw InvalidHexDataException::oddLength($hexString, $debugInfo);
        }

        // Validate hex characters only
        if (!ctype_xdigit($hexString)) {
            $debugInfo = self::generateDebugInfo($hexString, 'Invalid hex characters detected');
            throw InvalidHexDataException::invalidCharacters($hexString, $debugInfo);
        }

        // Additional check for minimum expected length (at least 32 characters for AES)
        if (strlen($hexString) < 32) {
            $debugInfo = self::generateDebugInfo($hexString, 'Hex string too short for AES decryption');
            throw InvalidHexDataException::corruptedData($hexString, $debugInfo);
        }

        // Attempt hex2bin conversion with error handling
        try {
            $binaryString = hex2bin($hexString);
            
            if ($binaryString === false) {
                $debugInfo = self::generateDebugInfo($hexString, 'hex2bin() returned false');
                throw InvalidHexDataException::invalidCharacters($hexString, $debugInfo);
            }

            $result = unpack('C*', $binaryString);
            
            if ($result === false || empty($result)) {
                $debugInfo = self::generateDebugInfo($hexString, 'unpack() failed or returned empty result');
                throw InvalidHexDataException::corruptedData($hexString, $debugInfo);
            }

            // Log successful conversion if debugging is enabled
            if (config('knet.debug_hex_conversion', false)) {
                logger()->debug('KPayClient | Successful hex conversion:', [
                    'input_length' => strlen($hexString),
                    'output_length' => count($result),
                    'input_preview' => substr($hexString, 0, 50) . '...'
                ]);
            }

            return $result;

        } catch (\ValueError $e) {
            // PHP 8+ throws ValueError for invalid hex strings
            $debugInfo = self::generateDebugInfo($hexString, 'ValueError: ' . $e->getMessage());
            throw InvalidHexDataException::invalidCharacters($hexString, $debugInfo);
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            $debugInfo = self::generateDebugInfo($hexString, 'Unexpected error: ' . $e->getMessage());
            throw InvalidHexDataException::corruptedData($hexString, $debugInfo);
        }
    }

    /**
     * Generate debug information for hex validation errors
     */
    public static function generateDebugInfo(?string $hexString, string $issue): string
    {
        if ($hexString === null) {
            return json_encode([
                'issue' => $issue,
                'input_type' => 'null',
                'timestamp' => now()->toISOString()
            ]);
        }

        $debugData = [
            'issue' => $issue,
            'input_length' => strlen($hexString),
            'input_type' => gettype($hexString),
            'first_50_chars' => substr($hexString, 0, 50),
            'last_50_chars' => strlen($hexString) > 50 ? substr($hexString, -50) : null,
            'contains_non_hex' => !ctype_xdigit($hexString),
            'is_odd_length' => strlen($hexString) % 2 !== 0,
            'character_analysis' => self::analyzeHexString($hexString),
            'timestamp' => now()->toISOString()
        ];

        return json_encode($debugData, JSON_PRETTY_PRINT);
    }

    /**
     * Analyze hex string for common issues
     */
    private static function analyzeHexString(string $hexString): array
    {
        $analysis = [
            'total_chars' => strlen($hexString),
            'valid_hex_chars' => 0,
            'invalid_chars' => [],
            'whitespace_count' => 0,
            'special_chars' => []
        ];

        for ($i = 0; $i < strlen($hexString); $i++) {
            $char = $hexString[$i];
            
            if (ctype_xdigit($char)) {
                $analysis['valid_hex_chars']++;
            } else {
                if (ctype_space($char)) {
                    $analysis['whitespace_count']++;
                } else {
                    $analysis['invalid_chars'][] = [
                        'char' => $char,
                        'position' => $i,
                        'ascii' => ord($char)
                    ];
                }
                
                if (in_array($char, ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '='])) {
                    $analysis['special_chars'][] = $char;
                }
            }
        }

        return $analysis;
    }

    /**
     * Utility method for debugging malformed hex data
     */
    public static function debugHexData(string $hexString): array
    {
        return [
            'original_data' => $hexString,
            'length' => strlen($hexString),
            'is_valid_hex' => ctype_xdigit($hexString),
            'is_even_length' => strlen($hexString) % 2 === 0,
            'trimmed_data' => trim($hexString),
            'analysis' => self::analyzeHexString($hexString),
            'sample_conversion_test' => self::testHexConversion(substr($hexString, 0, 10))
        ];
    }

    /**
     * Test hex conversion on a small sample
     */
    private static function testHexConversion(string $sample): array
    {
        try {
            if (strlen($sample) % 2 !== 0) {
                $sample = substr($sample, 0, -1); // Make even length
            }
            
            if (empty($sample)) {
                return ['status' => 'empty_sample'];
            }

            $result = hex2bin($sample);
            return [
                'status' => 'success',
                'sample' => $sample,
                'result_length' => strlen($result)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'sample' => $sample,
                'error' => $e->getMessage()
            ];
        }
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
        return urlencode($encrypted);
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

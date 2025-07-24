<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Exceptions\InvalidHexDataException;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Tests\TestCase;

class KPayClientHexValidationTest extends TestCase
{
    /**
     * Test successful hex conversion with valid data
     */
    public function test_successful_hex_conversion()
    {
        $validHex = 'abcdef123456789012345678901234567890abcdef123456789012345678901234';
        
        $result = KPayClient::hex2ByteArray($validHex);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals(strlen($validHex) / 2, count($result));
    }

    /**
     * Test empty hex string throws appropriate exception
     */
    public function test_empty_hex_string_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Empty or null hex data received from KNet gateway');
        
        KPayClient::hex2ByteArray('');
    }

    /**
     * Test null hex string throws appropriate exception
     */
    public function test_null_hex_string_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Empty or null hex data received from KNet gateway');
        
        KPayClient::hex2ByteArray(null);
    }

    /**
     * Test odd length hex string throws appropriate exception
     */
    public function test_odd_length_hex_string_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Invalid hex string length');
        
        KPayClient::hex2ByteArray('abcdef12345'); // 11 characters (odd)
    }

    /**
     * Test invalid hex characters throws appropriate exception
     */
    public function test_invalid_hex_characters_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Invalid hexadecimal characters detected');
        
        KPayClient::hex2ByteArray('abcdefg123456789012345678901234567890abcdef123456789012345678901234'); // contains 'g'
    }

    /**
     * Test hex string too short throws appropriate exception
     */
    public function test_hex_string_too_short_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Hex string too short for AES decryption');
        
        KPayClient::hex2ByteArray('abcdef12'); // Only 8 characters, less than 32 minimum
    }

    /**
     * Test hex string with special characters throws appropriate exception
     */
    public function test_hex_string_with_special_characters_throws_exception()
    {
        $this->expectException(InvalidHexDataException::class);
        $this->expectExceptionMessage('Invalid hexadecimal characters detected');
        
        KPayClient::hex2ByteArray('abcdef123456789012345678901234567890abcdef123456789012345678901234!@#');
    }

    /**
     * Test hex string with whitespace is trimmed and processed correctly
     */
    public function test_hex_string_with_whitespace_is_trimmed()
    {
        $hexWithWhitespace = "  abcdef123456789012345678901234567890abcdef123456789012345678901234  \n";
        
        $result = KPayClient::hex2ByteArray($hexWithWhitespace);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test debug hex data utility method
     */
    public function test_debug_hex_data_utility()
    {
        $hexString = 'abcdef123456789012345678901234567890abcdef123456789012345678901234';
        
        $debugInfo = KPayClient::debugHexData($hexString);
        
        $this->assertIsArray($debugInfo);
        $this->assertArrayHasKey('original_data', $debugInfo);
        $this->assertArrayHasKey('length', $debugInfo);
        $this->assertArrayHasKey('is_valid_hex', $debugInfo);
        $this->assertArrayHasKey('is_even_length', $debugInfo);
        $this->assertArrayHasKey('analysis', $debugInfo);
        
        $this->assertEquals($hexString, $debugInfo['original_data']);
        $this->assertEquals(strlen($hexString), $debugInfo['length']);
        $this->assertTrue($debugInfo['is_valid_hex']);
        $this->assertTrue($debugInfo['is_even_length']);
    }

    /**
     * Test debug info generation for invalid hex
     */
    public function test_debug_info_generation_for_invalid_hex()
    {
        $invalidHex = 'invalid_hex_string_with_special_chars!@#';
        
        $debugInfo = KPayClient::generateDebugInfo($invalidHex, 'Test issue');
        
        $this->assertIsString($debugInfo);
        
        $decoded = json_decode($debugInfo, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('issue', $decoded);
        $this->assertArrayHasKey('input_length', $decoded);
        $this->assertArrayHasKey('contains_non_hex', $decoded);
        $this->assertArrayHasKey('character_analysis', $decoded);
        
        $this->assertEquals('Test issue', $decoded['issue']);
        $this->assertTrue($decoded['contains_non_hex']);
    }

    /**
     * Test exception provides detailed error information
     */
    public function test_exception_provides_detailed_error_information()
    {
        $invalidHex = 'invalid_hex_with_special_chars!@#$';
        
        try {
            KPayClient::hex2ByteArray($invalidHex);
            $this->fail('Expected InvalidHexDataException was not thrown');
        } catch (InvalidHexDataException $e) {
            $this->assertEquals($invalidHex, $e->getHexData());
            $this->assertNotNull($e->getDebugInfo());
            
            $errorDetails = $e->getErrorDetails();
            $this->assertIsArray($errorDetails);
            $this->assertArrayHasKey('error_type', $errorDetails);
            $this->assertArrayHasKey('message', $errorDetails);
            $this->assertArrayHasKey('hex_data_length', $errorDetails);
            $this->assertArrayHasKey('debug_info', $errorDetails);
            
            $this->assertEquals('invalid_hex_data', $errorDetails['error_type']);
            $this->assertEquals(strlen($invalidHex), $errorDetails['hex_data_length']);
        }
    }

    /**
     * Test minimum valid hex string length
     */
    public function test_minimum_valid_hex_string_length()
    {
        // 32 characters is the minimum for AES
        $minValidHex = '12345678901234567890123456789012';
        
        $result = KPayClient::hex2ByteArray($minValidHex);
        
        $this->assertIsArray($result);
        $this->assertEquals(16, count($result)); // 32 hex chars = 16 bytes
    }

    /**
     * Test case insensitive hex validation
     */
    public function test_case_insensitive_hex_validation()
    {
        $upperCaseHex = 'ABCDEF123456789012345678901234567890ABCDEF123456789012345678901234';
        $mixedCaseHex = 'AbCdEf123456789012345678901234567890aBcDeF123456789012345678901234';
        
        $result1 = KPayClient::hex2ByteArray($upperCaseHex);
        $result2 = KPayClient::hex2ByteArray($mixedCaseHex);
        
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertNotEmpty($result1);
        $this->assertNotEmpty($result2);
    }
} 
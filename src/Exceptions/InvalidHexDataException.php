<?php

namespace Asciisd\Knet\Exceptions;

use Exception;

class InvalidHexDataException extends KnetException
{
    private ?string $hexData;

    private ?string $debugInfo;

    public function __construct(string $message, ?string $hexData = null, ?string $debugInfo = null, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->hexData = $hexData;
        $this->debugInfo = $debugInfo;
    }

    /**
     * Create exception for invalid hex characters
     */
    public static function invalidCharacters(string $hexData, ?string $debugInfo = null): self
    {
        return new self(
            'Invalid hexadecimal characters detected in KNet response data. The response contains non-hex characters that cannot be processed.',
            $hexData,
            $debugInfo
        );
    }

    /**
     * Create exception for empty hex data
     */
    public static function emptyData(?string $debugInfo = null): self
    {
        return new self(
            'Empty or null hex data received from KNet gateway. No data available for decryption.',
            null,
            $debugInfo
        );
    }

    /**
     * Create exception for odd-length hex string
     */
    public static function oddLength(string $hexData, ?string $debugInfo = null): self
    {
        return new self(
            'Invalid hex string length. Hex strings must have an even number of characters for proper byte conversion.',
            $hexData,
            $debugInfo
        );
    }

    /**
     * Create exception for hex string too short for AES
     */
    public static function tooShort(string $hexData, ?string $debugInfo = null): self
    {
        return new self(
            'Hex string too short for AES decryption. Minimum 32 characters required.',
            $hexData,
            $debugInfo
        );
    }

    /**
     * Create exception for corrupted response data
     */
    public static function corruptedData(string $hexData, ?string $debugInfo = null): self
    {
        return new self(
            'Corrupted response data detected from KNet gateway. The data appears to be truncated or malformed.',
            $hexData,
            $debugInfo
        );
    }

    /**
     * Get the hex data that caused the error
     */
    public function getHexData(): ?string
    {
        return $this->hexData;
    }

    /**
     * Get debug information
     */
    public function getDebugInfo(): ?string
    {
        return $this->debugInfo;
    }

    /**
     * Get formatted error details for logging
     */
    public function getErrorDetails(): array
    {
        return [
            'error_type' => 'invalid_hex_data',
            'message' => $this->getMessage(),
            'hex_data_length' => $this->hexData ? strlen($this->hexData) : 0,
            'hex_data_preview' => $this->hexData ? substr($this->hexData, 0, 100).'...' : null,
            'debug_info' => $this->debugInfo,
            'timestamp' => now()->toISOString(),
        ];
    }
}

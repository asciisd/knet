<?php

namespace Asciisd\Knet\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SignatureVerificationException extends HttpException
{
    private ?string $httpBody;
    private ?string $sigHeader;

    /**
     * Creates a new SignatureVerificationException instance.
     *
     * @param string $message The exception message.
     * @param string|null $httpBody The HTTP body as a string.
     * @param string|null $sigHeader The `KNet-Signature` HTTP header.
     */
    public function __construct(string $message, ?string $httpBody = null, ?string $sigHeader = null)
    {
        parent::__construct(403, $message);

        $this->httpBody = $httpBody;
        $this->sigHeader = $sigHeader;
    }

    /**
     * Creates a new instance of SignatureVerificationException.
     */
    public static function factory(string $message, ?string $httpBody = null, ?string $sigHeader = null): self
    {
        return new self($message, $httpBody, $sigHeader);
    }

    /**
     * Gets the HTTP body.
     */
    public function getHttpBody(): ?string
    {
        return $this->httpBody;
    }

    /**
     * Gets the `Knet-Signature` HTTP header.
     */
    public function getSigHeader(): ?string
    {
        return $this->sigHeader;
    }
}

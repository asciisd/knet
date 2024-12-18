<?php

namespace Asciisd\Knet\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SignatureVerificationException extends HttpException
{
    protected ?string $httpBody;
    protected ?string $sigHeader;

    /**
     * Creates a new SignatureVerificationException exception.
     *
     * @param string $message The exception message.
     * @param string|null $httpBody The HTTP body as a string.
     * @param string|null $sigHeader The `KNet-Signature` HTTP header.
     */
    public static function factory(string $message, ?string $httpBody = null, ?string $sigHeader = null): self
    {
        $instance = new static(403, $message);
        $instance->setHttpBody($httpBody);
        $instance->setSigHeader($sigHeader);
        return $instance;
    }

    /**
     * Gets the HTTP body as a string.
     */
    public function getHttpBody(): ?string
    {
        return $this->httpBody;
    }

    /**
     * Sets the HTTP body as a string.
     */
    public function setHttpBody(?string $httpBody): self
    {
        $this->httpBody = $httpBody;

        return $this;
    }

    /**
     * Gets the `Knet-Signature` HTTP header.
     *
     * @return string|null
     */
    public function getSigHeader(): ?string
    {
        return $this->sigHeader;
    }

    /**
     * Sets the `Knet-Signature` HTTP header.
     */
    public function setSigHeader(?string $sigHeader): self
    {
        $this->sigHeader = $sigHeader;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace DreamCommerce\Component\ShopAppstore\Api\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthenticationFailedException extends ApiException
{
    const CODE_INVALID_RESPONSE_BODY = 10;

    /**
     * @var string|null
     */
    private $errorCode;

    /**
     * @var string|null
     */
    private $errorDescription;

    /**
     * @param array $body
     * @param RequestInterface $httpRequest
     * @param ResponseInterface $httpResponse
     * @param Throwable|null $previous
     * @return AuthenticationFailedException
     */
    public static function forInvalidResponseBody(array $body, RequestInterface $httpRequest, ResponseInterface $httpResponse, Throwable $previous = null): self
    {
        $exception = new self('Authentication failed', self::CODE_INVALID_RESPONSE_BODY, $previous);
        $exception->httpRequest = $httpRequest;
        $exception->httpResponse = $httpResponse;

        if(isset($body['error'])) {
            $exception->errorCode = $body['error'];
        }
        if(isset($body['error_description'])) {
            $exception->errorDescription = $body['error_description'];
        }

        return $exception;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @return string|null
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}
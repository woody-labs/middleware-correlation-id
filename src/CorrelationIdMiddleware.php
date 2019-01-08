<?php

namespace Woody\Middleware\CorrelationId;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Woody\Http\Server\Middleware\MiddlewareInterface;

/**
 * Class CorrelationIdMiddleware
 *
 * @package Woody\Middleware\CorrelationId
 */
class CorrelationIdMiddleware implements MiddlewareInterface
{

    // Attribute name.
    const ATTRIBUTE_NAME = 'correlation-id';

    // CloudFlare ID.
    const HEADER_CF_RAY = 'CF-RAY';

    // Generic Header.
    const HEADER_CORRELATION_ID = 'X-Correlation-ID';

    // Request ID.
    const HEADER_REQUEST_ID = 'X-Request-ID';

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $responseHeader;

    /**
     * CorrelationIdMiddleware constructor.
     *
     * @param array $headers
     * @param string|false $responseHeader
     */
    public function __construct(array $headers = [], $responseHeader = null)
    {
        // Default values, order matter.
        if (empty($headers)) {
            $headers = [
                static::HEADER_CF_RAY,
                static::HEADER_CORRELATION_ID,
                static::HEADER_REQUEST_ID,
            ];
        }

        $this->headers = $headers;
        $this->responseHeader = $responseHeader ?? static::HEADER_CORRELATION_ID;
    }

    /**
     * @param bool $debug
     *
     * @return bool
     */
    public function isEnabled(bool $debug): bool
    {
        return true;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Extract correlation id from request.
        $correlationId = $this->getCorrelationId($request);

        // Attach correlation id to request for other middleware.
        $request = $request->withAttribute(static::ATTRIBUTE_NAME, $correlationId);

        // Dispatch request to other middleware.
        $response = $handler->handle($request);

        // Return response with correlation id added.
        if ($this->responseHeader) {
            return $response->withHeader($this->responseHeader, $correlationId);
        }

        // Return without added header.
        return $response;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \Exception
     */
    protected function getCorrelationId(ServerRequestInterface $request): string
    {
        foreach ($this->headers as $header) {
            if ($request->hasHeader($header)) {
                return $request->getHeaderLine($header);
            }
        }

        return Uuid::uuid4()->toString();
    }
}

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

    const ATTRIBUTE_NAME = 'correlation-id';

    // Generic Header.
    const HEADER_CORRELATION_ID = 'X-Correlation-ID';

    // CloudFlare ID.
    const HEADER_CF_RAY = 'CF-RAY';

    /**
     * CorrelationIdMiddleware constructor.
     */
    public function __construct()
    {
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
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader(self::HEADER_CF_RAY)) {
            $correlationId = $request->getHeader(self::HEADER_CF_RAY);
        } elseif ($request->hasHeader(self::HEADER_CORRELATION_ID)) {
            $correlationId = $request->getHeader(self::HEADER_CORRELATION_ID);
        } else {
            $correlationId = Uuid::uuid4()->toString();
        }

        $response = $handler->handle($request->withAttribute(self::ATTRIBUTE_NAME, $correlationId));

        return $response->withHeader(self::HEADER_CORRELATION_ID, $correlationId);
    }
}

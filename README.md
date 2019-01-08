# Middleware Correlation ID

A Correlation ID is a unique identifier value that is attached to requests and messages that allow reference to a particular transaction or event chain.


## Presentation

Some  loadbalancer or WAF provide such additional headers, so you just need to normalize its name a propagate it into your system (sub-request, logs, ...).

Correlation ID is detected as it :
- CF-RAY
- X-Correlation-ID
- X-Request-ID

If nothing is found, a new one is generated using a UUID generator and added as `X-Correlation-ID` and returned into the response.


## Implementation

Just add the middleware into your `dispatcher` pipeline.

````php
// @todo: generate request

$dispatcher = new Dispatcher();
$dispatcher->pipe(new CorrelationIdMiddleware());

// @todo: add other middleware

$response = $dispatcher->handle($request);
````

Correlation ID is also available as `attribute` under the `correlation-id` name.


Both `headers` and `response header` can be overridden.

````php
// @todo: generate request

$correlationMiddleware = new CorrelationIdMiddleware(
    [
        'X-Custom-Header',
        CorrelationIdMiddleware::HEADER_CORRELATION_ID,
    ],
    'X-Custom-Header'
);

$dispatcher = new Dispatcher();
$dispatcher->pipe($correlationMiddleware);

// @todo: add other middleware

$response = $dispatcher->handle($request);
````

Note: adding correlation id header in response can be skipped by specifying `false` as secondary parameter in the constructor.


## Documentation

[The Value of Correlation IDs](https://blog.rapid7.com/2016/12/23/the-value-of-correlation-ids/)

[Correlation IDs for microservices architectures](https://hilton.org.uk/blog/microservices-correlation-id)

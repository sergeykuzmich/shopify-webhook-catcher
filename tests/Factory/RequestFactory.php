<?php

namespace QA\Factory;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;

use function is_null;
use function is_string;

class RequestFactory extends \Slim\Psr7\Factory\RequestFactory implements RequestFactoryInterface
{
    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     * @param string|null $payload Content to attach to the HTTP request.
     * @param Headers|null $headers Headers to apply to the HTTP request.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri, $payload = '', $headers = null): RequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(
                'Parameter 4 of RequestFactory::createRequest() must be an object of Headers class.'
            );
        }

        $body = $this->streamFactory->createStream($payload);

        if (is_null($headers)) {
            $headers = new Headers();
        }

        if (!$headers instanceof Headers) {
            throw new InvalidArgumentException(
                'Parameter 2 of RequestFactory::createRequest() must be a string or a compatible UriInterface.'
            );
        }

        return new Request($method, $uri, $headers, [], [], $body);
    }
}

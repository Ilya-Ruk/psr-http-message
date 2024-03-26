<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

final class ServerRequest implements ServerRequestInterface
{
    use ServerRequestTrait;

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * @var string[]
     */
    private static array $supportedMethod = [
        self::METHOD_HEAD,
        self::METHOD_GET,
        self::METHOD_POST,
    ];

    /**
     * @param string|null $protocolVersion
     * @param array|null $headers
     * @param StreamInterface|null $body
     * @param string|null $requestTarget
     * @param string|null $method
     * @param UriInterface|null $uri
     * @param array|null $serverParams
     * @param array|null $cookieParams
     * @param array|null $queryParams
     * @param array|null $uploadedFiles
     * @param mixed|null $parsedBody
     * @param array|null $attributes
     */
    public function __construct(
        ?string $protocolVersion = null,
        ?array $headers = null,
        ?StreamInterface $body = null,
        ?string $requestTarget = null,
        ?string $method = null,
        ?UriInterface $uri = null,
        ?array $serverParams = null,
        ?array $cookieParams = null,
        ?array $queryParams = null,
        ?array $uploadedFiles = null,
        mixed $parsedBody = null,
        ?array $attributes = null
    ) {
        $this->protocolVersion = $this->prepareProtocolVersion($protocolVersion);
        $this->headers = $this->prepareHeaders($headers ?? $this->getAllHeaders());
        $this->body = $body ?? new Stream('php://input', 'rb');
        $this->requestTarget = $this->prepareRequestTarget($requestTarget);
        $this->method = $this->prepareMethod($method);
        $this->uri = $uri ?? $this->createUri();
        $this->serverParams = $serverParams ?? $_SERVER;
        $this->cookieParams = $cookieParams ?? $_COOKIE;
        $this->queryParams = $queryParams ?? $_GET;
        $this->uploadedFiles = $uploadedFiles ?? $_FILES; // TODO: prepareUploadedFiles
        $this->parsedBody = $parsedBody ?? $_POST; // TODO: prepareParsedBody
        $this->attributes = $attributes ?? [];
    }

    /**
     * @return array
     */
    private function getAllHeaders(): array
    {
        $headers = getallheaders();

        if ($headers === false) {
            throw new RuntimeException('Get all headers error!', 500);
        }

        return $headers;
    }

    /**
     * @param string|null $requestTarget
     * @return string
     */
    private function prepareRequestTarget(?string $requestTarget = null): string
    {
        if (is_null($requestTarget)) {
            $requestTarget = $_SERVER['REQUEST_URI'] ?? null;

            if (is_null($requestTarget)) {
                throw new RuntimeException('Request target not defined!', 500);
            }
        }

        return $requestTarget;
    }

    /**
     * @param string|null $method
     * @return string
     */
    private function prepareMethod(?string $method = null): string
    {
        if (is_null($method)) {
            $method = $_SERVER['REQUEST_METHOD'] ?? null;

            if (is_null($method)) {
                throw new RuntimeException('Request method not defined!', 500);
            }
        }

        if (!in_array($method, self::$supportedMethod)) {
            throw new RuntimeException('Method not supported!', 500);
        }

        return $method;
    }

    /**
     * @return UriInterface
     */
    private function createUri(): UriInterface
    {
        return (new Uri())
            ->withScheme($_SERVER['REQUEST_SCHEME'] ?? '')
            ->withHost($_SERVER['HTTP_HOST'] ?? '')
            ->withPort($_SERVER['SERVER_PORT'] ? (int)$_SERVER['SERVER_PORT'] : null)
            ->withPath($_SERVER['REQUEST_URI'] ? explode('?', $_SERVER['REQUEST_URI'])[0] : '')
            ->withQuery($_SERVER['QUERY_STRING'] ?? '');
    }
}

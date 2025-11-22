<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait RequestTrait
{
    use MessageTrait;

    /**
     * @var string
     */
    private string $requestTarget;

    /**
     * @var string
     */
    private string $method;

    /**
     * @var UriInterface
     */
    private UriInterface $uri;

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        if (empty($this->requestTarget)) {
            return '/';
        }

        return $this->requestTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $new = clone $this;

        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;

        $new->method = $method;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;

        $new->uri = $uri;

        $uriHost = $uri->getHost();
        $uriPort = $uri->getPort();

        if ($preserveHost) {
            if (!$new->hasHeader('Host') || empty($new->getHeaderLine('Host'))) {
                if (!empty($uriHost)) {
                    $newHeaderHost = $uriHost;

                    if (!is_null($uriPort)) {
                        $newHeaderHost .= ':' . $uriPort;
                    }

                    return $new->withHeader('Host', [$newHeaderHost]);
                }
            }

            return $new;
        }

        if (!empty($uriHost)) {
            $newHeaderHost = $uriHost;

            if (!is_null($uriPort)) {
                $newHeaderHost .= ':' . $uriPort;
            }

            return $new->withHeader('Host', [$newHeaderHost]);
        }

        return $new;
    }
}

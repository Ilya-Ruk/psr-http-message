<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

trait MessageTrait
{
    /**
     * @var string[]
     */
    private static array $supportedProtocolVersion = ['1.0', '1.1', '2'];

    /**
     * @var string
     */
    private string $protocolVersion;

    /**
     * @var string[][]
     */
    private array $headers;

    /**
     * @var StreamInterface
     */
    private StreamInterface $body;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if (!in_array($version, self::$supportedProtocolVersion)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Protocol version '%s' not supported! Protocol version must be in ('%s').",
                    $version,
                    implode("', '", self::$supportedProtocolVersion)
                )
            );
        }

        $new = clone $this;

        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $new = clone $this;

        if (is_array($value)) {
            $new->headers[$name] = $value;
        } else {
            $new->headers[$name][] = $value;
        }

        return $new;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $new = clone $this;

        if (is_array($value)) {
            $new->headers[$name] = array_merge($new->headers[$name], $value);
        } else {
            $new->headers[$name][] = $value;
        }

        return $new;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $new = clone $this;

        unset($new->headers[$name]);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;

        $new->body = $body;

        return $new;
    }

    /**
     * @param string|null $protocolVersion
     * @return string
     */
    private function prepareProtocolVersion(?string $protocolVersion = null): string
    {
        if (is_null($protocolVersion)) {
            $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? null;

            if (is_null($serverProtocol)) {
                throw new RuntimeException('Server protocol not defined!');
            }

            $protocolVersion = explode('/', $serverProtocol)[1] ?? null;

            if (is_null($protocolVersion)) {
                throw new RuntimeException('Server protocol version not defined!');
            }
        }

        if (!in_array($protocolVersion, self::$supportedProtocolVersion)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Protocol version '%s' not supported! Protocol version must be in ('%s').",
                    $protocolVersion,
                    implode("', '", self::$supportedProtocolVersion)
                )
            );
        }

        return $protocolVersion;
    }

    /**
     * @param array $headers
     * @return string[][]
     */
    private function prepareHeaders(array $headers): array
    {
        $resultHeaders = [];

        foreach ($headers as $name => $value) {
            if (is_array($value)) {
                $resultHeaders[$name] = $value;
            } else {
                $resultHeaders[$name][] = $value;
            }
        }

        return $resultHeaders;
    }
}

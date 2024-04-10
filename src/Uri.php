<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use RuntimeException;

final class Uri implements UriInterface
{
    /**
     * Map scheme to port
     */
    private const SCHEME_TO_PORT = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @var string
     */
    private string $scheme;

    /**
     * @var string
     */
    private string $user;

    /**
     * @var string|null
     */
    private ?string $password;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var int|null
     */
    private ?int $port;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $query;

    /**
     * @var string
     */
    private string $fragment;

    /**
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if ($uri === '') {
            return;
        }

        $uriParsed = parse_url($uri);

        if ($uriParsed === false) {
            throw new RuntimeException(sprintf("Parse URI '%s' error!", $uri), 500);
        }

        $scheme = $uriParsed['scheme'] ?? '';

        if (!array_key_exists($scheme, self::SCHEME_TO_PORT)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Scheme '%s' not supported! Scheme must be in ('%s').",
                    $scheme,
                    implode("', '", array_keys(self::SCHEME_TO_PORT))
                ),
                400
            );
        }

        $port = $uriParsed['port'] ?? null;

        $this->scheme = $scheme;
        $this->user = $uriParsed['user'] ?? '';
        $this->password = $uriParsed['pass'] ?? null;
        $this->host = $uriParsed['host'] ?? '';
        $this->port = $this->isStandardPort($scheme, $port) ? null : $port;
        $this->path = $uriParsed['path'] ?? '';
        $this->query = $uriParsed['query'] ?? '';
        $this->fragment = $uriParsed['fragment'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(string $scheme): UriInterface
    {
        if (!array_key_exists($scheme, self::SCHEME_TO_PORT)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Scheme '%s' not supported! Scheme must be in ('%s').",
                    $scheme,
                    implode("', '", array_keys(self::SCHEME_TO_PORT))
                ),
                400
            );
        }

        $new = clone $this;

        $new->scheme = $scheme;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        $userInfo = '';

        if (!empty($this->user)) {
            $userInfo .= $this->user;
        }

        if (!empty($this->password)) {
            $userInfo .= ':' . $this->password;
        }

        return $userInfo;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;

        $new->user = $user;
        $new->password = $password;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        $authority = '';

        $userInfo = $this->getUserInfo();

        if (!empty($userInfo)) {
            $authority .= $userInfo . '@';
        }

        if (!empty($this->host)) {
            $authority .= $this->host;
        }

        if (!empty($this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function withHost(string $host): UriInterface
    {
        $new = clone $this;

        $new->host = $host;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function withPort(?int $port): UriInterface
    {
        if (is_int($port)) {
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Port '%s' not supported! Port must be in range [1..65535].",
                        $port
                    ),
                    400
                );
            }
        }

        $new = clone $this;

        $new->port = $this->isStandardPort($this->scheme, $port) ? null : $port;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function withPath(string $path): UriInterface
    {
        $new = clone $this;

        $new->path = $path;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;

        $new->query = str_starts_with($query, '?') ? substr($query, 1) : $query;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;

        $new->fragment = str_starts_with($fragment, '#') ? substr($fragment, 1) : $fragment;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $uri = '';

        if (!empty($this->scheme)) {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();

        if (!empty($authority)) {
            $uri .= '//' . $authority;
        }

        if (!empty($this->path)) {
            if (!str_starts_with($this->path, '/') && !empty($authority)) {
                $uri .= '/' . $this->path;
            } elseif (str_starts_with($this->path, '//') && empty($authority)) {
                $uri .= '/' . ltrim($this->path, '/');
            } else {
                $uri .= $this->path;
            }
        }

        if (!empty($this->query)) {
            $uri .= '?' . $this->query;
        }

        if (!empty($this->fragment)) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * @param string $scheme
     * @param int|null $port
     * @return bool
     */
    private function isStandardPort(string $scheme, ?int $port): bool
    {
        $defaultPort = self::SCHEME_TO_PORT[$scheme];

        if ($port === $defaultPort) {
            return true;
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

trait ServerRequestTrait
{
    use RequestTrait;

    /**
     * @var array
     */
    private array $serverParams;

    /**
     * @var array
     */
    private array $cookieParams;

    /**
     * @var array
     */
    private array $queryParams;

    /**
     * @var array
     */
    private array $uploadedFiles;

    /**
     * @var null|array|object
     */
    private mixed $parsedBody;

    /**
     * @var array
     */
    private array $attributes;

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;

        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;

        $new->queryParams = $query;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new = clone $this;

        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_null($data) && !is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Parsed body type error (required 'null', 'array' or 'object', but given '%s')!",
                    gettype($data)
                )
            );
        }

        $new = clone $this;

        $new->parsedBody = $data;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name, $default = null)
    {
        $attributes = $this->getAttributes();

        return $attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }
}

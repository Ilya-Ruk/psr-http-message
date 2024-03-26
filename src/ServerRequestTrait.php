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
     * @param string $name
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getServerParam(string $name, mixed $defaultValue = null): mixed
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$name] ?? $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @param string $name
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getCookieParam(string $name, mixed $defaultValue = null): mixed
    {
        $cookieParams = $this->getCookieParams();

        return $cookieParams[$name] ?? $defaultValue;
    }

    /**
     * @return static
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
     * @param string $name
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getQueryParam(string $name, mixed $defaultValue = null): mixed
    {
        $queryParams = $this->getQueryParams();

        return $queryParams[$name] ?? $defaultValue;
    }

    /**
     * @return static
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
     * @return static
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
     * @param string $name
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getParsedBodyParam(string $name, mixed $defaultValue = null): mixed
    {
        $parsedBody = $this->getParsedBody();

        if (is_null($parsedBody)) {
            return $defaultValue;
        }

        if (is_object($parsedBody)) {
            return $parsedBody->$name ?? $defaultValue;
        }

        return $parsedBody[$name] ?? $defaultValue;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_null($data) && !is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Parsed body type error (required 'null', 'array' or 'object', but given '%s')!",
                    gettype($data)
                ),
                400
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
     * @return static
     * @inheritDoc
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @return static
     * @inheritDoc
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }
}

<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    use ResponseTrait;

    const DEFAULT_STATUS_CODE = 200; // OK

    /**
     * @param string|null $protocolVersion
     * @param array|null $headers
     * @param StreamInterface|null $body
     * @param int|null $statusCode
     * @param string|null $reasonPhrase
     */
    public function __construct(
        ?string $protocolVersion = null,
        ?array $headers = null,
        ?StreamInterface $body = null,
        ?int $statusCode = null,
        ?string $reasonPhrase = null,
    ) {
        $this->protocolVersion = $this->prepareProtocolVersion($protocolVersion);
        $this->headers = $this->prepareHeaders($headers ?? []);
        $this->body = $body ?? new Stream('php://temp', 'wb+');
        $this->statusCode = $this->prepareStatusCode($statusCode);
        $this->reasonPhrase = $reasonPhrase ?? '';
    }

    /**
     * @param int|null $statusCode
     * @return int
     */
    private function prepareStatusCode(?int $statusCode = null): int
    {
        if (is_null($statusCode)) {
            $statusCode = self::DEFAULT_STATUS_CODE;
        }

        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException(
                sprintf(
                    "Status code '%s' not supported! Status code must be in range [100..599]!",
                    $statusCode
                )
            );
        }

        return $statusCode;
    }
}

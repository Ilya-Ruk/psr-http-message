<?php

declare(strict_types=1);

namespace Rukavishnikov\Psr\Http\Message;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    private $resource = null;

    /**
     * @param string $stream
     * @param string $mode
     */
    public function __construct(string $stream = 'php://temp', string $mode = 'wb+')
    {
        $resource = @fopen($stream, $mode);

        if ($resource === false) {
            throw new RuntimeException(sprintf("Stream '%s' open error!", $stream), 500);
        }

        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new RuntimeException(sprintf("Stream '%s' type error!", $stream), 500);
        }

        $this->resource = $resource;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $resource = $this->detach();

        if (is_resource($resource)) {
            @fclose($resource);
        }
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $resource = $this->resource;

        $this->resource = null;

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $stat = @fstat($this->resource);

        if ($stat === false) {
            return null;
        }

        $size = $stat['size'] ?? null;

        if (is_null($size)) {
            return null;
        }

        return (int)$size;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream not defined!', 500);
        }

        $result = @ftell($this->resource);

        if ($result === false) {
            throw new RuntimeException(sprintf("Stream '%s' tell error!", $this->resource), 500);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return @feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        $seekable = $this->getMetadata('seekable') ?? false;

        return (bool)$seekable;
    }

    /**
     * @inheritDoc
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream not defined!', 500);
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException(sprintf("Stream '%s' not seekable!", $this->resource), 500);
        }

        $result = @fseek($this->resource, $offset, $whence);

        if ($result === -1) {
            throw new RuntimeException(sprintf("Stream '%s' seek error!", $this->resource), 500);
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');

        if (
            str_contains($mode, 'w')
            || str_contains($mode, 'a')
            || str_contains($mode, 'x')
            || str_contains($mode, 'c')
            || str_contains($mode, '+')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function write(string $string): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream not defined!', 500);
        }

        if (!$this->isWritable()) {
            throw new RuntimeException(sprintf("Stream '%s' not writable!", $this->resource), 500);
        }

        $result = @fwrite($this->resource, $string);

        if ($result === false || $result !== strlen($string)) {
            throw new RuntimeException(sprintf("Stream '%s' write error!", $this->resource), 500);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');

        if (
            str_contains($mode, 'r')
            || str_contains($mode, '+')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function read(int $length): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream not defined!', 500);
        }

        if (!$this->isReadable()) {
            throw new RuntimeException(sprintf("Stream '%s' not readable!", $this->resource), 500);
        }

        $str = @fread($this->resource, $length);

        if ($str === false) {
            throw new RuntimeException(sprintf("Stream '%s' read error!", $this->resource), 500);
        }

        return $str;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream not defined!', 500);
        }

        if (!$this->isReadable()) {
            throw new RuntimeException(sprintf("Stream '%s' not readable!", $this->resource), 500);
        }

        $result = @stream_get_contents($this->resource);

        if ($result === false) {
            throw new RuntimeException(sprintf("Stream '%s' get content error!", $this->resource), 500);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(?string $key = null)
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $metaData = @stream_get_meta_data($this->resource);

        if (is_null($key)) {
            return $metaData;
        }

        return $metaData[$key] ?? null;
    }
}

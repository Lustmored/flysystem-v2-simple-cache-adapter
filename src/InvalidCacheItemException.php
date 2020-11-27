<?php

namespace Lustmored\Flysystem\Cache;

use League\Flysystem\FilesystemException;
use RuntimeException;

class InvalidCacheItemException extends RuntimeException implements FilesystemException
{
    public static function withPathAndKey(string $path, string $key): self
    {
        return new self(
            sprintf(
                'Could not fetch key "%s" for path "%s"',
                $key,
                $path
            )
        );
    }

    public static function uninitialized(): self
    {
        return new self('Uninitialized cache item');
    }
}

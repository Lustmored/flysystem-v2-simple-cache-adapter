<?php

namespace Tests\Lustmored\Flysystem\Cache;

use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheFilesystemTest extends FilesystemAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $pool = new ArrayAdapter();
        $adapter = new InMemoryFilesystemAdapter();

        return new CacheAdapter($adapter, $pool);
    }
}

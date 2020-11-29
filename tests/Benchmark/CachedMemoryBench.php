<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CachedMemoryBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $pool = new ArrayAdapter();
        $adapter = new InMemoryFilesystemAdapter();

        return new CacheAdapter($adapter, $pool);
    }
}

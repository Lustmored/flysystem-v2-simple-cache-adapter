<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CachedMemoryBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $pool = new RedisAdapter(
            $redis
        );
        $adapter = new InMemoryFilesystemAdapter();

        return new CacheAdapter($adapter, $pool);
    }
}

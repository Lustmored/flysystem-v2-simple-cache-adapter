<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use function PHPUnit\Framework\fileExists;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CachedLocalBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $pool = new RedisAdapter(
            $redis
        );
        $dir = dirname(__DIR__).'/files/';
        if (!fileExists($dir)) {
            mkdir($dir);
        }

        $adapter = new LocalFilesystemAdapter($dir);

        return new CacheAdapter($adapter, $pool);
    }
}

<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

trait CachedTrait
{
    private static $pool;

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        self::$pool = new RedisAdapter(
            $redis
        );

        return new CacheAdapter(parent::createFilesystemAdapter(), self::$pool);
    }

    public function init(): void
    {
        parent::init();

        $this->benchListContents();
    }

    public function cleanup(): void
    {
        self::$pool->clear();
    }
}

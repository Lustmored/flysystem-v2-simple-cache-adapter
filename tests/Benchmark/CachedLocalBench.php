<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Lustmored\Flysystem\Cache\CacheAdapter;
use function PHPUnit\Framework\fileExists;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CachedLocalBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $pool = new ArrayAdapter();
        $dir = dirname(__DIR__).'/files/';
        if (!fileExists($dir)) {
            mkdir($dir);
        }

        $adapter = new LocalFilesystemAdapter($dir);

        return new CacheAdapter($adapter, $pool);
    }
}

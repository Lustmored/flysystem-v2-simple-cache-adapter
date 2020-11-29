<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function PHPUnit\Framework\fileExists;

class LocalBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $dir = dirname(__DIR__).'/files/';
        if (!fileExists($dir)) {
            mkdir($dir);
        }

        return new LocalFilesystemAdapter($dir);
    }
}

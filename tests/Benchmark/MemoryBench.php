<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

class MemoryBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}

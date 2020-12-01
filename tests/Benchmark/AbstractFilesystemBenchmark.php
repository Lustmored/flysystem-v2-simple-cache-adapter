<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Visibility;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;

/**
 * @BeforeClassMethods({"initFiles"})
 * @AfterMethods({"cleanup"})
 * @BeforeMethods({"init"})
 */
abstract class AbstractFilesystemBenchmark
{
    protected Filesystem $fileSystem;
    protected string $dir;

    abstract protected static function createFilesystemAdapter(): FilesystemAdapter;

    public function init(): void
    {
        $this->fileSystem = new Filesystem(static::createFilesystemAdapter());
        $this->dir = 'bench';
    }

    public static function initFiles(): void
    {
        $filesystem = new Filesystem(static::createFilesystemAdapter());
        $filesystem->createDirectory('bench');
        for ($i = 0; $i < 100; ++$i) {
            if (!$filesystem->fileExists("bench/{$i}.txt")) {
                $filesystem->write("bench/{$i}.txt", sha1('bench'.$i));
            }
        }
    }

    public function cleanup(): void
    {
    }

    public function removeFiles(): void
    {
        $this->fileSystem->deleteDirectory($this->dir);
    }

    private function runMultipleRandomized(
        int $iterations,
        int $rand_limit,
        callable $fn
    ): void {
        for ($i = 0; $i < $iterations; ++$i) {
            $file = random_int(0, $rand_limit);
            try {
                $fn("{$this->dir}/{$file}.txt", $i);
            } catch (Exception $e) {
            }
        }
    }

    public function benchCopyAndDelete(): void
    {
        for ($i = 0; $i < 99; ++$i) {
            $this->fileSystem->copy(
                "{$this->dir}/{$i}.txt",
                "{$this->dir}/".($i + 100).'.txt'
            );
        }
        for ($i = 0; $i < 99; ++$i) {
            $this->fileSystem->delete(
                "{$this->dir}/".($i + 100).'.txt'
            );
        }
    }

    public function benchListContents(): void
    {
        $this->fileSystem->listContents($this->dir, true)->toArray();
    }

    public function benchMove(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $this->fileSystem->move(
                "{$this->dir}/{$i}.txt",
                "{$this->dir}/".($i + 100).'.txt'
            );
        }
        for ($i = 0; $i < 100; ++$i) {
            $this->fileSystem->move(
                "{$this->dir}/".($i + 100).'.txt',
                "{$this->dir}/{$i}.txt"
            );
        }
    }

    public function benchRandomFileExists(): void
    {
        $this->runMultipleRandomized(
            1000,
            199,
            fn ($path) => $this->fileSystem->fileExists($path)
        );
    }

    public function benchRandomFileSize(): void
    {
        $this->runMultipleRandomized(
            1000,
            99,
            fn ($path) => $this->fileSystem->fileSize($path)
        );
    }

    public function benchRandomLastModified(): void
    {
        $this->runMultipleRandomized(
            1000,
            99,
            fn ($path) => $this->fileSystem->lastModified($path)
        );
    }

    public function benchRandomMimeType(): void
    {
        $this->runMultipleRandomized(
            1000,
            99,
            fn ($path) => $this->fileSystem->mimeType($path)
        );
    }

    public function benchRandomRead(): void
    {
        $this->runMultipleRandomized(
            100,
            99,
            fn ($path) => $this->fileSystem->read($path)
        );
    }

    public function benchRandomSetVisibility(): void
    {
        $this->runMultipleRandomized(
            100,
            99,
            fn ($path) => $this->fileSystem->setVisibility(
                $path,
                Visibility::PUBLIC
            )
        );
    }

    public function benchRandomVisibility(): void
    {
        $this->runMultipleRandomized(
            1000,
            99,
            fn ($path) => $this->fileSystem->visibility($path)
        );
    }

    public function benchRandomWrite(): void
    {
        $this->runMultipleRandomized(
            100,
            99,
            fn ($path, $i) => $this->fileSystem->write($path, "overwrite{$i}")
        );
    }
}

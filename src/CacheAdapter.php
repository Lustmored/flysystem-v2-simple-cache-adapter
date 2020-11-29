<?php

namespace Lustmored\Flysystem\Cache;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToReadFile;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheAdapter implements FilesystemAdapter
{
    private FilesystemAdapter $adapter;
    private CacheItemPoolInterface $cachePool;

    public function __construct(
        FilesystemAdapter $adapter,
        CacheItemPoolInterface $cachePool
    ) {
        $this->adapter = $adapter;
        $this->cachePool = $cachePool;
    }

    private function getItem(string $path): FilesystemCacheItem
    {
        $key = sha1($path);
        try {
            return new FilesystemCacheItem(
                $this->cachePool,
                $this->cachePool->getItem($key),
                $path
            );
        } catch (InvalidArgumentException $exception) {
            throw InvalidCacheItemException::withPathAndKey($path, $key);
        }
    }

    public function fileExists(string $path): bool
    {
        $item = $this->getItem($path);
        if ($item->exists()) {
            return true;
        }
        $fileExists = $this->adapter->fileExists($path);
        if ($fileExists) {
            $item->initialize()->save();
        }

        return $fileExists;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($path, $contents, $config);

        $item = $this->getItem($path)->initialize();
        $metadata = $item->getMetadata();
        $metadata->setLastModified(time());
        if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
            $metadata->setVisibility($config->get(Config::OPTION_VISIBILITY));
        }
        $item->save();
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($path, $contents, $config);

        $item = $this->getItem($path)->initialize();
        $metadata = $item->getMetadata();
        $metadata->setLastModified(time());
        if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
            $metadata->setVisibility($config->get(Config::OPTION_VISIBILITY));
        }
        $item->save();
    }

    public function read(string $path): string
    {
        $item = $this->getItem($path);
        try {
            $contents = $this->adapter->read($path);

            if (!$item->exists()) {
                $item->initialize()->save();
            }

            return $contents;
        } catch (UnableToReadFile $exception) {
            if ($item->exists()) {
                $item->delete();
            }

            throw $exception;
        }
    }

    public function readStream(string $path)
    {
        $item = $this->getItem($path);
        try {
            $contents = $this->adapter->readStream($path);

            if (!$item->exists()) {
                $item->initialize()->save();
            }

            return $contents;
        } catch (UnableToReadFile $exception) {
            if ($item->exists()) {
                $item->delete();
            }

            throw $exception;
        }
    }

    public function delete(string $path): void
    {
        $this->adapter->delete($path);

        $item = $this->getItem($path);
        if ($item->exists()) {
            $item->delete();
        }
    }

    public function deleteDirectory(string $path): void
    {
        /** @var StorageAttributes $storageAttributes */
        foreach ($this->adapter->listContents($path, true) as $storageAttributes) {
            if ($storageAttributes->isFile()) {
                $item = $this->getItem($storageAttributes->path());
                if ($item->exists()) {
                    $item->delete();
                }
            }
        }

        $this->adapter->deleteDirectory($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);

        $item = $this->getItem($path)->loadOrInitialize();
        $item->getMetadata()->setVisibility($visibility);
        $item->save();
    }

    public function visibility(string $path): FileAttributes
    {
        $item = $this->getItem($path);
        if ($item->exists()) {
            $metadata = $item->load()->getMetadata();
            if (null !== $metadata->getVisibility()) {
                return $metadata->buildFileAttributes($path);
            }
        } else {
            $metadata = $item->initialize()->getMetadata();
        }

        $fileAttributes = $this->adapter->visibility($path);

        $metadata->setFromFileAttributes($fileAttributes);
        $item->save();

        return $fileAttributes;
    }

    public function mimeType(string $path): FileAttributes
    {
        $item = $this->getItem($path);
        if ($item->exists()) {
            $metadata = $item->load()->getMetadata();
            if (null !== $metadata->getMimeType()) {
                return $metadata->buildFileAttributes($path);
            }
        } else {
            $metadata = $item->initialize()->getMetadata();
        }

        $fileAttributes = $this->adapter->mimeType($path);

        $metadata->setFromFileAttributes($fileAttributes);
        $item->save();

        return $fileAttributes;
    }

    public function lastModified(string $path): FileAttributes
    {
        $item = $this->getItem($path);
        if ($item->exists()) {
            $metadata = $item->load()->getMetadata();
            if (null !== $metadata->getLastModified()) {
                return $metadata->buildFileAttributes($path);
            }
        } else {
            $metadata = $item->initialize()->getMetadata();
        }

        $fileAttributes = $this->adapter->lastModified($path);

        $metadata->setFromFileAttributes($fileAttributes);
        $item->save();

        return $fileAttributes;
    }

    public function fileSize(string $path): FileAttributes
    {
        $item = $this->getItem($path);
        if ($item->exists()) {
            $metadata = $item->load()->getMetadata();
            if (null !== $metadata->getFileSize()) {
                return $metadata->buildFileAttributes($path);
            }
        } else {
            $metadata = $item->initialize()->getMetadata();
        }

        $fileAttributes = $this->adapter->fileSize($path);

        $metadata->setFromFileAttributes($fileAttributes);
        $item->save();

        return $fileAttributes;
    }

    public function listContents(string $path, bool $deep): iterable
    {
        /** @var StorageAttributes|FileAttributes $storageAttributes */
        foreach ($this->adapter->listContents($path, $deep) as $storageAttributes) {
            if ($storageAttributes->isFile()) {
                $item = $this->getItem($storageAttributes->path())->loadOrInitialize();
                $item->getMetadata()->setFromFileAttributes($storageAttributes);
                $item->save();
            }

            yield $storageAttributes;
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);

        $from = $this->getItem($source);
        if ($from->exists()) {
            $to = $this->getItem($destination);
            $to->initialize()->setMetadata($from->load()->getMetadata())->save();
            $from->delete();
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);

        $from = $this->getItem($source);
        if ($from->exists()) {
            $to = $this->getItem($destination);
            $to->initialize()->setMetadata($from->load()->getMetadata())->save();
        }
    }
}

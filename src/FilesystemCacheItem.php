<?php

namespace Lustmored\Flysystem\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class FilesystemCacheItem {
    private CacheItemPoolInterface $cachePool;
    private CacheItemInterface $item;
    private string $path;
    private ?FileMetadataCache $metadata;

    public function __construct(
        CacheItemPoolInterface $cachePool,
        CacheItemInterface $item,
        string $path
    ){
        $this->cachePool = $cachePool;
        $this->item = $item;
        $this->path = $path;
    }

    public function exists(): bool
    {
        try {
            return $this->cachePool->hasItem($this->item->getKey());
        }catch (InvalidArgumentException $exception) {
            throw InvalidCacheItemException::withPathAndKey($this->path, $this->item->getKey());
        }
    }

    public function initialize(): self
    {
        $this->metadata = new FileMetadataCache();

        return $this;
    }

    public function load(): self
    {
        $this->metadata = $this->item->get();

        return $this;
    }

    public function save(): self
    {
        $this->item->set($this->metadata);
        $this->cachePool->save($this->item);
        return $this;
    }

    public function delete(): self
    {
        try {
            $this->cachePool->deleteItem($this->item->getKey());
        }catch (InvalidArgumentException $exception) {
            throw InvalidCacheItemException::withPathAndKey($this->path, $this->item->getKey());
        }

        return $this;
    }

    public function getMetadata(): FileMetadataCache {
        if(!isset($this->metadata)) {
            throw InvalidCacheItemException::uninitialized();
        }
        return $this->metadata;
    }

    public function setMetadata(FileMetadataCache $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}

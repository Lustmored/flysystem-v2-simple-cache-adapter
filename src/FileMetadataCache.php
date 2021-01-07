<?php

namespace Lustmored\Flysystem\Cache;

use League\Flysystem\FileAttributes;

class FileMetadataCache
{
    /** @var int|null */
    private $lastModified = null;
    /** @var string|null */
    private $mimeType = null;
    /** @var int|null */
    private $fileSize = null;
    /** @var string|null */
    private $visibility = null;

    public function getLastModified(): ?int
    {
        return $this->lastModified;
    }

    public function setLastModified(int $lastModified): FileMetadataCache
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): FileMetadataCache
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): FileMetadataCache
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): FileMetadataCache
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function setFromFileAttributes(FileAttributes $fileAttributes): FileMetadataCache
    {
        if ($lastModifier = $fileAttributes->lastModified()) {
            $this->lastModified = $lastModifier;
        }

        if ($mimeType = $fileAttributes->mimeType()) {
            $this->mimeType = $mimeType;
        }

        if ($fileSize = $fileAttributes->fileSize()) {
            $this->fileSize = $fileSize;
        }

        if ($visibility = $fileAttributes->visibility()) {
            $this->visibility = $visibility;
        }

        return $this;
    }

    public function buildFileAttributes(string $path): FileAttributes
    {
        return new FileAttributes(
            $path,
            $this->fileSize,
            $this->visibility,
            $this->lastModified,
            $this->mimeType
        );
    }
}

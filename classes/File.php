<?php

namespace SiteMap\File;

abstract class File implements FileInterface
{
    private $filePath = '';

    public function __construct($filePath)
    {
        $this->setPath($filePath);
    }

    public function read(): string
    {
        return file_get_contents($this->getPath());
    }

    public function write(string $data): bool
    {
        file_put_contents($this->getPath(), $data);
        return true;
    }

    public function create(): bool
    {
        return touch($this->getPath());
    }

    public function delete(): bool
    {
        return unlink($this->getPath());
    }

    public function setPath(string $filePath): void
    {
        $this->filePath = $filePath;

        if (!file_exists($filePath) && !$this->create()) {
            die('i am die');
        }

    }

    public function getPath(): string
    {
        return $this->filePath;
    }
}
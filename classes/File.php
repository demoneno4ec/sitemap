<?php

namespace SiteMap\File;

abstract class File implements FileInterface
{
    private $name = '';
    private $extension = '';
    private $fullName = '';

    public function __construct($name, $extension)
    {
        $this->name= $name;
        $this->extension = $extension;
        $this->setFullName($name, $extension);
    }

    public function read(): string
    {
        return file_get_contents($this->getPath());
    }

    public function write(string $data, array $options = []): bool
    {
        if (!empty($options['append']) && $options['append'] === true) {
            file_put_contents($this->getPath(), $data, FILE_APPEND);
        } else {
            file_put_contents($this->getPath(), $data);
        }
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

    public function setFullName(string $name, string $extension): void
    {
        $this->fullName = $name . $extension;

        if (!file_exists($this->getPath()) && !$this->create()) {
            die('i am die');
        }

    }

    public function getPath(): string
    {
        return $this->fullName;
    }
}
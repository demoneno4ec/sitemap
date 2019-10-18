<?php

namespace SiteMap\File;

interface FileInterface
{
    public function read();
    public function write(string $data, array $options = []):bool;
    public function create():bool;
    public function delete():bool;

    public function setFullName(string $name, string $extension):void;

    public function getPath():string;
}
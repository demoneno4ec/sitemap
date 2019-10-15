<?php

namespace SiteMap\File;

interface FileInterface
{
    public function read();
    public function write(string $data):bool;
    public function create():bool;
    public function delete():bool;

    public function setPath(string $filePath):void;

    public function getPath():string;
}
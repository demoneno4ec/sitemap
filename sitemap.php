<?php

use SiteMap\SiteMap\SiteMap;

require_once 'functions.php';
require 'vendor/autoload.php';
require_once 'classes/PhpQuery.php';
require_once 'interfaces/FileInterface.php';
require_once 'classes/File.php';
require_once 'classes/Link.php';
require_once 'classes/SiteMap.php';

if (empty($argv)) {
    error('Похоже register_argc_argv отключен.');
    die();
}

if (empty($argv[1])) {
    error('Передайте проверяемый url');
    die();
}

$url = $argv[1];


$siteMap = new SiteMap($url);
$siteMap->requestLinks();
$data = $siteMap->getData();
$siteMap->write($data);
echo 'we are';

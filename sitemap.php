<?php

require_once 'functions.php';
require 'vendor/autoload.php';
require_once 'classes/PhpQuery.php';
require_once 'classes/SiteMap.php';

echo 'hello pink world';

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
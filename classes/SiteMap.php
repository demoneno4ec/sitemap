<?php

namespace SiteMap\SiteMap;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Rct567\DomQuery\DomQuery;
use SiteMap\File\File;
use SiteMap\Link\Link;

class SiteMap extends File
{
    private $url = '';

    private const siteMapFile = 'sitemap.xml';

    private $links = [];

    private $links_checked = [];
    private $links_added = [];

    private $dataPut = '';

    public function __construct($url)
    {
        parent::__construct(self::siteMapFile);
        $this->setUrl($url);
        $this->setDataPut();
    }


    public function requestLinks(): void
    {
        $url = $this->getUrl();
        $this->requestUrl($url);
        $this->dataAppend('</urlset>');
    }

    private function getLinksPage(string $page, string $rootPath, string $pagePath = ''):array
    {
        $document = new DomQuery($page);
        $obLinks = $document->find('a');
        $links = [];

        foreach ($obLinks as $key => $obLink) {
            $strLink = $obLink->attr('href');

            try {
                $link = new Link($strLink, $rootPath, $pagePath);
            }catch(Exception $e){
                info($strLink .' '. $e->getMessage());
                continue;
            }

            $fullPath = $link->getFullPath();

            if (in_array($fullPath, $this->links_checked, true)){
                continue;
            }
            if (in_array($fullPath, $this->links_added, true)){
                continue;
            }
            $this->addLink($link);
            $this->addAddedUrl($fullPath);
            $links[] = $link->getFullPath();
        }

        return $links;
    }

    private function requestUrl($pagePath, $rootPath = ''): bool
    {
        $rootPath = $rootPath ? : $pagePath;

        $client = new Client();
        try {
            $response = $client->request('GET', $pagePath);

            $this->links_checked[] = $pagePath;
        } catch (GuzzleException $e) {
            print_r($e->getMessage());
            return false;
        }

        $this->addCheckedUrl($pagePath);

        $this->addDataUrl($pagePath);

        $content = $response->getBody();
        $pages = $this->getLinksPage($content, $rootPath, $pagePath);

        foreach ($pages as $page){
            $this->requestUrl($page, $this->getUrl());
        }

        return true;

    }


    /**
    list add item
     */

    private function addCheckedUrl(string $pagePath): void
    {
        $this->links_checked[] = $pagePath;
    }

    private function addAddedUrl(string $pagePath): void
    {
        $this->links_added[] = $pagePath;
    }

    private function addLink($link):void
    {
        $this->links[] = $link;
    }


    /**
    data string
     */
    private function addDataUrl(string $url): void
    {
        $stringAppend = '  <url>'. PHP_EOL .
            '    <loc>'. htmlentities ($url) .'</loc>'. PHP_EOL .
//            '    <changefreq>'.$freq.'</changefreq>'. PHP_EOL .
//            '    <priority>'.$priority.'</priority>'. PHP_EOL .
            '  </url>'. PHP_EOL;

        $this->dataAppend($stringAppend);
    }

    private function dataAppend(string $string): void
    {
        $this->dataPut .= $string;
    }

    /**
     * setters
     */
    /**
     * @param $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    private function setDataPut(): void
    {
        $stringAppend = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"'. PHP_EOL .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'. PHP_EOL .
            'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'. PHP_EOL;

        $this->dataAppend($stringAppend);
    }

    /**
    getters
     */

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getData(): string{
        return $this->dataPut;
    }
}
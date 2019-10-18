<?php

namespace SiteMap;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Rct567\DomQuery\DomQuery;
use SiteMap\File\File;
use SiteMap\Link\Link;

class SiteMap extends File
{
    private $url = '';

    private const siteMapFile = 'sitemap.xml';

    private $links_added = [];

    public function __construct($url)
    {
        parent::__construct(self::siteMapFile);
        $this->setUrl($url);
        $this->writeBefore();
    }


    public function generate()
    {
        $url = $this->url;
        $this->writeLink($url);
        $this->writeLinksOnPage($url);
        success('sitemap generated');
        $this->writeAfter();
    }

    private function writeLinksOnPage(string $url)
    {
        // Получить html по url
        $htmlPage = $this->getHtmlByUrl($url);

        //Получить все ссылки на странице
        $links = $this->getLinks($htmlPage, $this->url, $url);

        //Записать полученные ссылки
        $this->writeLinks($links);

        foreach ($links as $link) {
            $this->writeLinksOnPage($link->getFullPath());
        }

        return true;

    }

    private function getHtmlByUrl(string $url)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            error(print_r($e->getMessage()));
            return false;
        }

        return $response->getBody();

    }

    /**
     * @param  string  $html
     * @param  string  $rootPath
     * @param  string  $url
     * @return Link[]
     */
    private function getLinks(string $html, string $rootPath, string $url): array
    {
        $document = new DomQuery($html);
        $obLinks = $document->find('a');
        $links = [];

        /** @var DomQuery $obLink */
        foreach ($obLinks as $key => $obLink) {
            $href = $obLink->attr('href');

            try {
                $link = new Link($href, $rootPath, $url);
            } catch (Exception $e) {
                info($href.' '.$e->getMessage());
                continue;
            }

            $fullPath = $link->getFullPath();

            if (in_array($fullPath, $this->links_added, true)) {
                continue;
            }

            $links[] = $link;
        }

        return array_unique($links);
    }

    /**
     * @param  Link[]  $links
     */
    private function writeLinks(array $links)
    {
        foreach ($links as $link) {
            $fullPath = $link->getFullPath();
            $this->writeLink($fullPath);
            $this->setFilePath();
        }
    }

    private function writeLink(string $link):void
    {
        $options = [
            'append' => true
        ];

        $this->addAddedLink($link);

        $string = '  <url>'.PHP_EOL.
            '    <loc>'.htmlentities($link).'</loc>'.PHP_EOL.
//            '    <changefreq>'.$freq.'</changefreq>'. PHP_EOL .
//            '    <priority>'.$priority.'</priority>'. PHP_EOL .
            '  </url>'.PHP_EOL;
        $this->write($string, $options);
    }

    private function writeBefore(): void
    {
        $before = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"'.PHP_EOL.
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.PHP_EOL.
            'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'.PHP_EOL;

        $this->write($before);
    }

    private function writeAfter(): void
    {
        $options = [
            'append' => true
        ];

        $after = '</urlset>';
        $this->write($after, $options);
    }

    /**
     * list add item
     */

    private function addAddedLink(string $pagePath): void
    {
        $this->links_added[] = $pagePath;
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


    /**
     * getters
     */

    public function getUrl(): string
    {
        return $this->url;
    }

    private function setFilePath()
    {
//        $countLinkAdded = count($this->links_added);
//        $indexFile = count($this->links_added) % 50;
//        $index = count($this->links_added) / 50;
//        var_dump($countLinkAdded);
//        var_dump($indexFile);
//        var_dump((int) $index);
    }
}
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

    private $fileName = 'sitemap';
    private const fileExtension = '.xml';
    private $fullName = 'sitemap.xml';

    private $links_added = [];

    private $fileIndex = 0;
    private $maxSize = 50 * 1024 * 1024;


    public function __construct($url)
    {
        parent::__construct($this->fileName, self::fileExtension);
        $this->setUrl($url);
    }


    public function generate(): void
    {
        $url = $this->getUrl();
        $this->writeBefore();
        $this->writeLink($url);
        $this->writeLinksOnPage($url);
        success('sitemap generated');
        if ($this->fileIndex > 0) {
            $this->generateMultiSitemap();
        }else{
            $this->writeAfter();
        }
    }

    private function writeLinksOnPage(string $url): bool
    {
        // Получить html по url
        $htmlPage = $this->getHtmlByUrl($url);

        if (empty($htmlPage)) {
            return false;
        }
        //Получить все ссылки на странице
        $links = $this->getLinks($htmlPage, $this->getUrl(), $url);

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
            error(print_r($e->getMessage(), 1));
            return '';
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
    private function writeLinks(array $links): void
    {
        foreach ($links as $link) {
            $fullPath = $link->getFullPath();
            success($fullPath);
            $this->writeLink($fullPath);
        }
    }

    private function writeLink(string $link): void
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
        $this->checkMaxParams();
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

    private function checkMaxParams(): void
    {
        $countLinkAdded = count($this->links_added);

        $index = (int) ($countLinkAdded / 50000);

        if ($index !== $this->fileIndex || filesize($this->fullName) > $this->maxSize) {
            $this->fileIndex++;
            $newFileName = $this->fileName.'_'.$this->fileIndex.self::fileExtension;
            copy($this->fullName, $newFileName);
            file_put_contents($newFileName, '</urlset>', FILE_APPEND);
            $this->writeBefore();
        }
    }

    private function generateMultiSitemap()
    {
        $this->fileIndex++;
        $newFileName = $this->fileName.'_'.$this->fileIndex.self::fileExtension;
        copy($this->fullName, $newFileName);
        file_put_contents($newFileName, '</urlset>', FILE_APPEND);

        $this->writeMultiBefore();
        for ($index = $this->fileIndex; $index > 0;  $index--) {
            $sitemapName = $this->fileName.'_'.$index.self::fileExtension;
            $this->writeSitemapLink($this->getUrl() . '/' . $sitemapName);
        }
        $this->writeMultiAfter();
    }

    private function writeMultiBefore()
    {
        $before =
            '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        $this->write($before);
    }
    private function writeMultiAfter(): void
    {
        $options = [
            'append' => true
        ];
        $this->write('</sitemapindex>', $options);
    }

    private function writeSitemapLink(string $link): void
    {
        $options = [
            'append' => true
        ];

        $string = '  <sitemap>'.PHP_EOL.
            '    <loc>'.htmlentities($link).'</loc>'.PHP_EOL.
            '  </sitemap>'.PHP_EOL;

        $this->write($string, $options);
    }
}
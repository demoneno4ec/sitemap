<?php

namespace SiteMap\Link;

use RuntimeException;

class Link
{
    private $value = '';
    private $rootPath = '';
    private $fullPath = '';

    public function __construct(string $strLink, $rootPath, $url)
    {
        $this->setRootPath($rootPath);
        $this->setValue($strLink);

        if (empty($this->getValue())) {
            throw new RuntimeException('Ссылка пуста');
        }

        $this->setFullPath($url);
    }

    public function __toString(): string
    {
        return $this->fullPath;
    }

    private function validLink(string $link): bool
    {
        $clearLink = $this->clearGetParams($link);

        if (empty($link)) {
            return false;
        }

        $invalidLinks = [
            'javascript:;',
            '#',
        ];

        $invalidStartLinks = [
            'tel:',
            '//',
        ];

        $invalidExtensions = [
            '.pdf'
        ];
        if (in_array($link, $invalidLinks, true)) {
            return false;
        }


        if ($this->checkFullPathLink($link)) {
            return true;
        }

        foreach ($invalidStartLinks as $invalidStartLink) {
            if (mb_strpos($link, $invalidStartLink) === 0) {
                return false;
            }
        }

        $extension = substr($clearLink, strrpos($clearLink, '.'));

        if (in_array($extension, $invalidExtensions, 1)) {
            return false;
        }

        if ($this->checkOtherResource($link)) {
            return false;
        }

        return true;
    }

    private function formatLink(string $pagePath, string $link): string
    {
        if ($this->checkAbsolutePathLink($link)) {
            return $this->getRootPath().$link;
        }

        if ($this->checkFullPathLink($link)) {
            return $link;
        }

        return $pagePath.'/'.$link;
    }

    private function filterLink(string $link): string
    {
        return rtrim(ltrim($link), " \/\t\n\r\0\x0B");
    }

    /**
     * Checkers
     */

    /**
     * @param  string  $link
     * @return bool
     */
    private function checkFullPathLink(string $link): bool
    {
        return mb_strpos($link, $this->getRootPath()) === 0;
    }

    private function checkAbsolutePathLink(string $link): bool
    {
        return (strpos($link, '/') === 0);
    }

    /**
     * Getters
     */
    public function getValue(): string
    {
        return $this->value;
    }

    private function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * Setters
     * @param  string  $link
     */
    private function setValue(string $link): void
    {
        $link = $this->filterLink($link);

        if ($this->validLink($link)) {
            $this->value = $link;
        }
    }

    private function setRootPath(string $rootPath): void
    {
        $this->rootPath = $this->filterLink($rootPath);
    }

    private function setFullPath($pagePath): void
    {
        $value = $this->getValue();
        $this->fullPath = $this->formatLink($pagePath, $value);
    }

    private function clearGetParams($url):string
    {
        return preg_replace('/^([^?]+)(\?.*?)?(#.*)?$/', '$1$3', $url);
    }

    private function checkOtherResource(string $link): bool
    {
        return (mb_strpos($link, 'http://') === 0) || (mb_strpos($link, 'https://') === 0);
    }
}
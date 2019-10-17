<?php

namespace SiteMap\Link;

use RuntimeException;

class Link
{
    private $value = '';
    private $rootPath = '';
    private $fullPath = '';

    public function __construct(string $strLink, $rootPath, $pagePath = '')
    {
        $this->setRootPath($rootPath);
        $this->setValue($strLink);

        if (empty($this->getValue())) {
            throw new RuntimeException('Ссылка пуста');
        }

        $this->setFullPath($pagePath);
    }


    private function validLink(string $link): bool
    {
        if (empty($link)) {
            return false;
        }

        $invalidLinks = [
            'javascript:;',
            '#',
        ];

        if (in_array($link, $invalidLinks, true)) {
            return false;
        }

        if ($this->checkFullPathLink($link)) {
            return true;
        }

        if (
            $this->checkAbsolutePathLink($link)
            && isset($link[1])
            && $link[1] === '/') {
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
        $link = rtrim($link);

        return $link;
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
     */
    private function setValue($link): void
    {
        $link = $this->filterLink($link);

        if ($this->validLink($link)) {
            $this->value = $link;
        }
    }

    private function setRootPath(string $rootPath): void
    {
        $this->rtrim($rootPath);

        $this->rootPath = $rootPath;
    }

    private function setFullPath($pagePath): void
    {
        $value = $this->getValue();
        $this->fullPath = $this->formatLink($pagePath, $value);
    }
}
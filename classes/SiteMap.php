<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class SiteMap
{
    private $url = '';

    public function __construct($url)
    {
        $this->setUrl($url);
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function requestLinks(): void
    {
        $url = $this->getUrl();
        $content = $this->requestUrl($url);
        print_r($content['statusCode']);
        $document = phpQuery::newDocument($content['content']);
        $links = $document->find('a');

        foreach ($links as $reqLink) {
//            $link = pq()
            $link = pq($reqLink)->attr('href');
            info($link);
            success(filter_var($link, FILTER_VALIDATE_URL));
        }
    }

    private function requestUrl($url): array
    {
        $client = new Client();
        try {
            $response = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            print_r($e);
            die();
        }

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => $response->getBody(),
        ];

    }


}
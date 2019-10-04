<?php

namespace App\Service\Crawler;

use App\Service\Crawler\Strategy\GundemKibrisStrategy;
use App\Service\Crawler\Strategy\KibrisPostasiStrategy;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Link;

class WebsiteCrawler implements Crawler
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var \App\Service\Crawler\Strategy\CrawlerStrategy
     */
    private $crawlerStrategy;

    public function __construct(
        //Client $httpClient
        //,
        //GundemKibrisStrategy $crawlerStrategy
        KibrisPostasiStrategy $crawlerStrategy
    ) {
        $this->httpClient      = new Client();
        $this->crawlerStrategy = $crawlerStrategy;
    }

    public function fetchPostLinksOn(string $url): array
    {
        $response = $this->httpClient->get($url, ['curl' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0]]);

        $domCrawler = new DomCrawler((string) $response->getBody(), $url);

        $hyperlinks = $domCrawler->filter('a')->links();

        $hyperlinks = array_map(function (Link $link) {
            return $link->getUri();
        }, $hyperlinks);

        return array_filter($hyperlinks, [$this->crawlerStrategy, "isHyperlinkToPost"]);
    }
}
<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Service\Crawler\Strategy\CrawlerStrategy;
use App\Service\Crawler\Strategy\TeBilisimStrategy;
use App\Service\Crawler\Strategy\KibrisPostasiStrategy;
use App\Service\Crawler\Strategy\StrategyFactory;
use App\Types\Category;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Link;

class WebsiteCrawler implements Crawler
{
    private const GUZZLE_DEFAULT_OPTIONS = [
        'curl' => [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
        ],
    ];

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var \App\Service\Crawler\Strategy\StrategyFactory
     */
    private $strategyFactory;

    public function __construct(
        //Client $httpClient
        //,
        //TeBilisimStrategy $crawlerStrategy
        StrategyFactory $strategyFactory
    ) {
        $this->httpClient      = new Client();
        $this->strategyFactory = $strategyFactory;
    }

    public function fetchPostLinksFromProvider(NewsProvider $provider, NewsProviderCategory ...$categoriesToFetch): array
    {
        $categoriesToFetch = empty($categoriesToFetch)
            ? $provider->getCategories()
            : $categoriesToFetch;

        $crawlerStrategy = $this->strategyFactory->getStrategyFor($provider);

        $postLinks = [];
        foreach ($categoriesToFetch as $providerCategory) {
            $fetchedLinks = $this->fetchInternalLinksOn(
                implode('/', [$provider->getUrl(), $providerCategory->getPath()])
            );

            $fetchedPostLinks = array_filter(
                $fetchedLinks,
                function ($internalLink) use ($crawlerStrategy, $providerCategory) {
                    return $crawlerStrategy->isHyperlinkToCategoryPost($internalLink, $providerCategory);
                }
            );

            $postLinks = array_merge($postLinks, $fetchedPostLinks);
        }

        return array_unique($postLinks);
    }

    private function fetchInternalLinksOn(string $url): array
    {
        $domCrawler = $this->getDomCrawler($url);
        $hyperlinks = $domCrawler->filter('a')->links();

        $hyperlinks = array_map(function (Link $link) {
            return $link->getUri();
        }, $hyperlinks);

        $hyperlinks = array_filter(
            $hyperlinks,
            function ($link) use ($url) {
                $host = parse_url($url)["host"];

                $parsed_link = parse_url($link);

                if (!isset($parsed_link["host"])) {
                    return false;
                }

                return $parsed_link["host"] === $host;
            }
        );

        return $hyperlinks;
    }

    private function getDomCrawler(string $url): DomCrawler
    {
        $response   = $this->httpClient->get($url, self::GUZZLE_DEFAULT_OPTIONS);
        $domCrawler = new DomCrawler((string)$response->getBody(), $url);

        return $domCrawler;
    }
}
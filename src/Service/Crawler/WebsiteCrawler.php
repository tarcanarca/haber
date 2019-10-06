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
use Psr\Log\LoggerInterface;
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

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        //Client $httpClient
        //,
        //TeBilisimStrategy $crawlerStrategy
        StrategyFactory $strategyFactory,
        LoggerInterface $logger
    ) {
        $this->httpClient      = new Client();
        $this->strategyFactory = $strategyFactory;
        $this->logger          = $logger;
    }

    public function fetchPostLinksFromProvider(NewsProvider $provider, NewsProviderCategory ...$categoriesToFetch): array
    {
        $categoriesToFetch = empty($categoriesToFetch)
            ? $provider->getCategories()
            : $categoriesToFetch;

        $crawlerStrategy = $this->strategyFactory->getStrategyFor($provider);

        $postLinks = [];

        $this->logger->notice(sprintf(
            "Crawling: %s (%s) - %d categories: %s",
            $provider->getName(),
            $provider->getUrl(),
            count($categoriesToFetch),
            implode(", ", $categoriesToFetch)
        ));

        foreach ($categoriesToFetch as $providerCategory) {
            //$this->logger->info("Category: " . $providerCategory);

            $fetchedLinks = $this->fetchInternalLinksOn(
                implode('/', [$provider->getUrl(), $providerCategory->getPath()])
            );

            $fetchedPostLinks = array_filter(
                $fetchedLinks,
                function ($internalLink) use ($crawlerStrategy, $providerCategory) {
                    return $crawlerStrategy->isHyperlinkToCategoryPost($internalLink, $providerCategory);
                }
            );

            //$this->logger->info(sprintf("%d post links fetched.", count($fetchedPostLinks)));

            $postLinks = array_merge($postLinks, $fetchedPostLinks);
        }

        $postLinks = array_unique($postLinks);

        $this->logger->info(sprintf(
            "%d post links fetched for %d categories.",
            count($postLinks),
            count($categoriesToFetch)
        ));

        return $postLinks;
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
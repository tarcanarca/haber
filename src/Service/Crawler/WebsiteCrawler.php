<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Service\Crawler\Strategy\StrategyFactory;
use App\ValueObject\WebsiteContents;
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
        StrategyFactory $strategyFactory
    ) {
        $this->httpClient      = new Client();
        $this->strategyFactory = $strategyFactory;
    }

    public function getHtmlContents(string $url): WebsiteContents
    {
        $crawler = $this->getDomCrawler($url);

        $crawler->filter('script, noscript, style, embed, input, iframe, form, area')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $crawler->filterXPath('comment()')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $crawler->filter('[style]')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->attributes->getNamedItem('style')->nodeValue = '';
            }
        });

        $htmlContents = $crawler->html();

        return new WebsiteContents($url, $htmlContents);
    }

    public function fetchPostLinksFromProvider(NewsProvider $provider, iterable $categoriesToFetch): array
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

        $postLinks = array_unique($postLinks);

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
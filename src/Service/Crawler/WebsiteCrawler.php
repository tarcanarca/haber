<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Service\Crawler\Exception\CrawlException;
use App\Service\Crawler\Strategy\StrategyFactory;
use App\ValueObject\WebsiteContents;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise;
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

    /**
     * @throws \App\Service\Crawler\Exception\CrawlException
     */
    public function fetchPostLinksFromProvider(NewsProvider $provider, iterable $categoriesToFetch): array
    {
        $categoriesToFetch = empty($categoriesToFetch)
            ? $provider->getCategories()
            : $categoriesToFetch;

        $crawlerStrategy = $this->strategyFactory->getStrategyFor($provider);

        $postLinks = [];

        $promises = [];
        foreach ($categoriesToFetch as $providerCategory) {
            $categoryPageUrl = implode('/', [$provider->getUrl(), $providerCategory->getPath()]);
            $categoryPath    = $providerCategory->getPath();

            $promises[$categoryPath . ' ' . $categoryPageUrl]
                = $this->httpClient->getAsync($categoryPageUrl, self::GUZZLE_DEFAULT_OPTIONS);
        }

        try {
            $results = Promise\unwrap($promises);
        } catch (ConnectException $exception) {
            throw CrawlException::cannotLoadCategoryPages($exception);
        }
        /**
         * @var $categoryAndUrl string
         * @var $response       \Psr\Http\Message\ResponseInterface
         */
        foreach ($results as $categoryAndUrl => $response) {
            list($categoryPath, $url) = explode(' ', $categoryAndUrl);

            $domCrawler = new DomCrawler((string)$response->getBody(), $url);

            $fetchedLinks =$this->fetchInternalLinksOn($domCrawler);

            $fetchedPostLinks = array_filter(
                $fetchedLinks,
                function ($internalLink) use ($crawlerStrategy, $categoryPath) {
                    return $crawlerStrategy->isHyperlinkToCategoryPost($internalLink, $categoryPath);
                }
            );

            $postLinks = array_merge($postLinks, $fetchedPostLinks);
        }

        $postLinks = array_unique($postLinks);

        return $postLinks;
    }

    private function fetchInternalLinksOn(DomCrawler $domCrawler): array
    {
        $hyperlinks = $domCrawler->filter('a')->links();
        $url        = $domCrawler->getUri();

        $hyperlinks = array_map(function (Link $link) {
            return $link->getUri();
        }, $hyperlinks);

        $hyperlinks = array_filter(
            $hyperlinks,
            function ($link) use ($url) {
                $host        = parse_url($url)["host"];
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
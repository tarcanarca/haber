<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Service\Crawler\Exception\CrawlException;
use App\Service\Crawler\Strategy\StrategyFactory;
use App\ValueObject\WebsiteContents;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpAsyncClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class AsyncCrawler implements Crawler
{
    use CrawlerTools;

    /**
     * @var \Http\Client\HttpAsyncClient
     */
    private $httpClient;

    /**
     * @var \App\Service\Crawler\Strategy\StrategyFactory
     */
    private $strategyFactory;

    public function __construct(
        HttpAsyncClient $httpClient,
        StrategyFactory $strategyFactory
    ) {
        $this->httpClient      = $httpClient;
        $this->strategyFactory = $strategyFactory;
    }

    /**
     * @param \Psr\Http\Message\UriInterface[] $urls
     *
     * @return WebsiteContents[]
     *
     * @throws \App\Service\Crawler\Exception\CrawlException
     */
    public function getHtmlContentsConcurrently(array $urls): array
    {
        foreach ($urls as $url) {
            $promises[$url] = $this->httpClient->sendAsyncRequest(new Request("GET", $url));
        }

        try {
            $results = Promise\unwrap($promises);
        } catch (\Exception $exception) {
            throw CrawlException::cannotLoadCategoryPages($exception);
        }

        $contents = [];
        foreach ($results as $url => $response) {
            $domCrawler = new DomCrawler((string)$response->getBody(), $url);

            try {
                $htmlContents = $this->getTrimmedContents($domCrawler)->html();
            } catch (\InvalidArgumentException $e) {
                // Invalid result, not HTML?
                // @todo: Cover this case
                continue;
            }

            $contents[] = new WebsiteContents($url, $htmlContents);
        }

        return $contents;
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

        $postLinks = $promises = [];
        foreach ($categoriesToFetch as $providerCategory) {
            $categoryPageUrl = $this->getCategoryPageUrl($provider, $providerCategory);
            $categoryPath    = $providerCategory->getPath();

            $promises[$categoryPath . ' ' . $categoryPageUrl]
                = $this->httpClient->sendAsyncRequest(new Request("GET", $categoryPageUrl));
        }

        try {
            /**
             * Wait until all requests are completed.
             *
             * @todo: Keep iterating through promises and process right after one is fulfilled.
             */
            $results = Promise\unwrap($promises);
        } catch (\Exception $exception) {
            throw CrawlException::cannotLoadCategoryPages($provider, $exception);
        }

        /**
         * @var $categoryAndUrl string
         * @var $response       \Psr\Http\Message\ResponseInterface
         */
        foreach ($results as $categoryAndUrl => $response) {
            list($categoryPath, $url) = explode(' ', $categoryAndUrl);

            $domCrawler = new DomCrawler((string)$response->getBody(), $url);

            $fetchedLinks = $this->fetchInternalLinksOn($domCrawler);

            // @todo: Move to trait as well.
            $fetchedPostLinks = array_filter(
                $fetchedLinks,
                function ($internalLink) use ($crawlerStrategy, $categoryPath) {
                    return $crawlerStrategy->isHyperlinkToCategoryPost($internalLink, $categoryPath);
                }
            );

            $postLinks = array_merge($postLinks, $fetchedPostLinks);
        }

        return array_unique($postLinks);
    }
}
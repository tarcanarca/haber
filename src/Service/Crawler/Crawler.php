<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\ValueObject\WebsiteContents;

interface Crawler
{
    /**
     * @param NewsProvider           $provider
     * @param NewsProviderCategory[] $categoriesToFetch
     *
     * @return WebsiteContents[]
     */
    public function fetchPostLinksFromProvider(NewsProvider $provider, iterable $categoriesToFetch): array;

    /**
     * @param \Psr\Http\Message\UriInterface[] $urls
     *
     * @return WebsiteContents[]
     *
     * @throws \App\Service\Crawler\Exception\CrawlException
     */
    public function getHtmlContentsConcurrently(array $urls): array;
}
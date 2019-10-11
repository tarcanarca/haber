<?php

namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;

interface Crawler
{
    public function fetchPostLinksFromProvider(NewsProvider $provider, iterable $categoriesToFetch): array;

    //public function getPostLinks(string $url): array;

    //public function getPostImages(string $postUrl): array;
}
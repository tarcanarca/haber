<?php

namespace App\Service\Crawler\Strategy;

use App\Entity\NewsProviderCategory;

interface CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, NewsProviderCategory $category): bool;
}
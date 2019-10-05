<?php

namespace App\Service\Crawler\Strategy;

use App\Entity\NewsProviderCategory;

class KibrisPostasiStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, NewsProviderCategory $category): bool
    {
        $searchLinkPattern = sprintf('/\/%s\/n[0-9]{6,}-/', $category->getPath());

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
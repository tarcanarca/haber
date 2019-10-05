<?php

namespace App\Service\Crawler\Strategy;

use App\Entity\NewsProviderCategory;

class TeBilisimStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, NewsProviderCategory $category): bool
    {
        $searchLinkPattern = sprintf('/\/%s\/.*h[0-9]{6,}.html$/', $category->getPath());

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
<?php

namespace App\Service\Crawler\Strategy;

class TeBilisimStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, string $categoryPath): bool
    {
        $searchLinkPattern = sprintf('/\/%s\/.*h[0-9]{6,}.html$/', $categoryPath);

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
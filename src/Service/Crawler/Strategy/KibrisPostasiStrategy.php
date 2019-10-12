<?php

namespace App\Service\Crawler\Strategy;

class KibrisPostasiStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, string $categoryPath): bool
    {
        $searchLinkPattern = sprintf('/\/%s\/n[0-9]{6,}-/', $categoryPath);

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
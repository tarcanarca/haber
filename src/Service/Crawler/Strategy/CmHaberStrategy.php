<?php

namespace App\Service\Crawler\Strategy;

/**
 * CM HABER is a popular news content management system used amongst multiple websites in Cyprus.
 */
class CmHaberStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, string $categoryPath): bool
    {
        $searchLinkPattern = '/-[0-9]{6,}h.htm$/';

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
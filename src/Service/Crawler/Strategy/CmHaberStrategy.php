<?php

namespace App\Service\Crawler\Strategy;

use App\Entity\NewsProviderCategory;

class CmHaberStrategy implements CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, NewsProviderCategory $category): bool
    {
        $searchLinkPattern = '/-[0-9]{6,}h.htm$/';

        return preg_match($searchLinkPattern, $hyperlink) === 1;
    }
}
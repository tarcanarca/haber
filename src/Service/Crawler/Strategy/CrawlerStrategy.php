<?php

namespace App\Service\Crawler\Strategy;

interface CrawlerStrategy
{
    public function isHyperlinkToCategoryPost(string $hyperlink, string $categoryPath): bool;
}
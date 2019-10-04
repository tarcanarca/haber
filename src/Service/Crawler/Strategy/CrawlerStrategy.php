<?php

namespace App\Service\Crawler\Strategy;

interface CrawlerStrategy
{
    public function isHyperlinkToPost(string $hyperlink): bool;

    public function isHyperlinkToCategoryPage(string $hyperlink): bool;
}
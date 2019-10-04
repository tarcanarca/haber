<?php

namespace App\Service\Crawler\Strategy;

class GundemKibrisStrategy implements CrawlerStrategy
{
    public function isHyperlinkToPost(string $hyperlink): bool
    {
        return preg_match('/^http(s|):\/\/(www.|)gundemkibris.com.*h[0-9]{6,}.html$/', $hyperlink) === 1;
    }

    public function isHyperlinkToCategoryPage(string $hyperlink): bool
    {
        return false;
    }
}
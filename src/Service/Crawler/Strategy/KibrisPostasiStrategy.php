<?php

namespace App\Service\Crawler\Strategy;

class KibrisPostasiStrategy implements CrawlerStrategy
{
    public function isHyperlinkToPost(string $hyperlink): bool
    {
        return preg_match('/^http(s|):\/\/(www.|)kibrispostasi.com.*\/n[0-9]{6,}-/', $hyperlink) === 1;
    }

    public function isHyperlinkToCategoryPage(string $hyperlink): bool
    {
        return false;
    }
}
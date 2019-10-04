<?php

namespace App\Service\Crawler;

interface Crawler
{
    public function fetchPostLinksOn(string $url): array;

    //public function getPostLinks(string $url): array;

    //public function getPostImages(string $postUrl): array;
}
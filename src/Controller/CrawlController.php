<?php

namespace App\Controller;

use App\Service\Crawler\WebsiteCrawler;
use Symfony\Component\HttpFoundation\Response;

class CrawlController
{
    /**
     * @var \App\Service\Crawler\WebsiteCrawler
     */
    private $crawler;

    public function __construct(WebsiteCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function index(): Response
    {
        $postLinks = $this->crawler->fetchPostLinksOn("http://www.kibrispostasi.com/c35-KIBRIS_HABERLERI");

        return new Response(implode("<br>", $postLinks));
    }
}
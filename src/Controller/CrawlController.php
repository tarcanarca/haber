<?php

namespace App\Controller;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Service\Crawler\WebsiteCrawler;
use App\Types\Category;
use App\Types\ProviderType;
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
        $postLinks = [];

        foreach ($this->getProvidersToCrawl() as $provider) {
            $postLinks = array_merge(
                $postLinks,
                $this->crawler->fetchPostLinksFromProvider($provider)
            );
        }

        return new Response(implode("<br>", $postLinks));
    }

    /**
     * @return \App\Entity\NewsProvider[]
     */
    private function getProvidersToCrawl(): array
    {
        return [
            (new NewsProvider(ProviderType::KIBRIS_POSTASI(), "Kibris Postasi", "http://www.kibrispostasi.com"))
                ->setCategories([
                    new NewsProviderCategory(Category::KIBRIS(), "c35-KIBRIS_HABERLERI"),
                ]),
            (new NewsProvider(ProviderType::TE_BILISIM(), "Gundem Kibris", "http://www.gundemkibris.com"))
                ->setCategories([
                    new NewsProviderCategory(Category::DUNYA(), "dunya"),
                    new NewsProviderCategory(Category::KIBRIS(), "kibris"),
                ]),
            (new NewsProvider(ProviderType::CM_HABER(), "Detay Kibris", "http://www.detaykibris.com"))
                ->setCategories([
                    new NewsProviderCategory(Category::DUNYA(), "dunya-haberleri-45hk.htm"),
                    new NewsProviderCategory(Category::KIBRIS(), "kibris-haberleri-7hk.htm"),
                ]),
        ];
    }
}
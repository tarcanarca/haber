<?php

namespace App\Controller;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Service\Crawler\WebsiteCrawler;
use App\Service\Parser\ParserFactory;
use App\Service\Parser\PostItemParser;
use App\Types\Category;
use App\Types\ProviderType;
use Symfony\Component\HttpFoundation\Response;

class CrawlController
{
    /**
     * @var \App\Service\Crawler\WebsiteCrawler
     */
    private $crawler;

    /**
     * @var \App\Service\Parser\PostItemParser
     */
    private $postItemParser;

    /**
     * @var \App\Service\Parser\ParserFactory
     */
    private $postItemParserFactory;

    public function __construct(WebsiteCrawler $crawler, ParserFactory $postItemParserFactory)
    {
        $this->crawler       = $crawler;
        $this->postItemParserFactory = $postItemParserFactory;
    }

    public function index(): Response
    {
        $postLinks = [];
        $i = 0;

        foreach ($this->getProvidersToCrawl() as $provider) {
            $postLinks = array_merge(
                $postLinks,
                $this->crawler->fetchPostLinksFromProvider($provider)
            );

            foreach ($postLinks as $postLink) {
                $html = $this->crawler->getHtmlContents($postLink);

                // persist??
                // persist??

                $postItems[] = $this->postItemParserFactory->getParserFor($provider)->parsePost($html);

                //$this->postRepository->persist($postItem);

                // stop after 1
                if ($i++ > 4) {
                    break 2;
                }
            }
        }

        //return new Response(implode("<br>", $postLinks));
        return new Response("<pre>" . print_r($postItems, true) . "</pre>");
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
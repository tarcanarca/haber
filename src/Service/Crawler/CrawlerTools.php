<?php
namespace App\Service\Crawler;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Link;

trait CrawlerTools
{
    private function getTrimmedContents(DomCrawler $crawler): DomCrawler
    {
        $crawler->filter('script, noscript, style, embed, input, iframe, form, area')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $crawler->filterXPath('comment()')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $crawler->filter('[style]')->each(function (DomCrawler $crawler) {
            foreach ($crawler as $node) {
                $node->attributes->getNamedItem('style')->nodeValue = '';
            }
        });

        return $crawler;
    }

    private function getCategoryPageUrl(
        NewsProvider $provider,
        NewsProviderCategory $providerCategory
    ): string {
        return implode('', [$provider->getUrl(), $providerCategory->getPath()]);
    }

    private function fetchInternalLinksOn(DomCrawler $domCrawler): array
    {
        $hyperlinks = $domCrawler->filter('a')->links();
        $url        = $domCrawler->getUri();

        $hyperlinks = array_map(function (Link $link) {
            return $link->getUri();
        }, $hyperlinks);

        $hyperlinks = array_filter(
            $hyperlinks,
            function ($link) use ($url) {
                $host        = parse_url($url)["host"];
                $parsed_link = parse_url($link);

                if (!isset($parsed_link["host"])) {
                    return false;
                }

                return $parsed_link["host"] === $host;
            }
        );

        return $hyperlinks;
    }
}
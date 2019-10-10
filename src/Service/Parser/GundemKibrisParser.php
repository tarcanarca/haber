<?php

namespace App\Service\Parser;

use App\Entity\PostItem;
use App\ValueObject\Url;
use App\ValueObject\WebsiteContents;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Webmozart\Assert\Assert;

class GundemKibrisParser implements PostItemParser
{
    use ParserTools;

    public function parsePost(WebsiteContents $contents): PostItem
    {
        $domCrawler = $this->getDomCrawler($contents->getHtmlContents());

        $heading    = $domCrawler->filter("article h1.title")->text();
        $content    = $domCrawler->filter("#newsbody p")->text();;
        $createdAt  = $domCrawler->filter(".tarih-degistir")->attr('data-date');

        $content = preg_replace("/\sKıbrıs Postası( -{1,3} .{1,}){0,1}/", "", $content);
        $content = ltrim($content);

        $createdAt = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $createdAt, new \DateTimeZone("+0100"));

        $postItem = new PostItem();
        $postItem->setHeading($heading)
            ->setContents($content)
            ->setCreatedAt($createdAt->setTimezone(new \DateTimeZone("UTC")));


        try {
            $lastUpdatedAt = $domCrawler->filter("#newstext .muted.pull-right span")->text();
            $lastUpdatedAt = \DateTimeImmutable::createFromFormat("d.m.Y H:i", $lastUpdatedAt, new \DateTimeZone("+0100")) ?? null;
            $postItem->setLastUpdatedAt($lastUpdatedAt->setTimezone(new \DateTimeZone("UTC")));
        } catch (\InvalidArgumentException $exception) {
            // nevermind
        }

        return $postItem;
    }

    public function getPostMainImageUrl(WebsiteContents $contents): string
    {
        $domCrawler = $this->getDomCrawler($contents->getHtmlContents());

        $data = $domCrawler->filterXPath("//meta[@property='og:image']")->extract(['content']);

        return $data[0];
    }

    public function getPostGalleryImageUrls(WebsiteContents $contents): array
    {
        // @todo: Check!
        return [];
    }

    // Move to trait
    private function getDomCrawler(string $postHtmlContent): \Symfony\Component\DomCrawler\Crawler
    {
        $domCrawler = new DomCrawler();
        $domCrawler->addHtmlContent($postHtmlContent);

        return $domCrawler;
    }

    public function getProviderIdForPost(WebsiteContents $contents): string
    {
        return $this->matchPostIdFromUrl('/\-h([0-9]{6,9}).html/', $contents->getUrl());
    }
}
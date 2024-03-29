<?php

namespace App\Service\Parser;

use App\Entity\PostItem;
use App\ValueObject\Url;
use App\ValueObject\WebsiteContents;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Webmozart\Assert\Assert;

class KibrisPostasiParser implements PostItemParser
{
    use ParserTools;

    public function parsePost(WebsiteContents $contents): PostItem
    {
        $domCrawler = $this->getDomCrawler($contents->getHtmlContents());

        $heading   = $domCrawler->filter("#content h1.title")->text();
        $spot      = $domCrawler->filter("#content .brief")->text();
        $content   = $domCrawler->filter("#content .full")->text();
        $createdAt = $domCrawler->filter("#main_left .int_roll_top:nth-child(2)")->text();

        $spot    = ltrim($spot);
        $content = preg_replace("/\sKıbrıs Postası( -{1,3} .{1,}){0,1}/", "", $content);
        $content = ltrim($content);

        setlocale(LC_TIME, "tr_TR");
        $parsedDate = strptime($createdAt, '%d %B %Y,');
        preg_match('/[0-9]{2}:[0-9]{2}$/', $createdAt, $timeParts);

        $createdAt = \DateTimeImmutable::createFromFormat(
            "d m Y H:i",
            $parsedDate["tm_mday"] . " " .
            $parsedDate["tm_mon"] . " " .
            ($parsedDate["tm_year"] + 1900) . " " .
            $timeParts[0],
            new \DateTimeZone("+0100")
        );

        $createdAt = $createdAt->setTimezone(new \DateTimeZone("UTC"));

        $postItem = new PostItem();
        $postItem->setHeading($heading)
                 ->setSpot($spot)
                 ->setContents($content)
                 ->setCreatedAt($createdAt);

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
        $domCrawler = $this->getDomCrawler($contents->getHtmlContents());

        $imageUrls  = $domCrawler->filter("#content img")->extract(["src"]);
        $imagePaths = array_filter($imageUrls, function ($url) {
            return strstr($url, "/upload/news");
        });

        $imagePaths = array_map(
            function ($url) {
                preg_match("/\/upload\/news.*/", $url, $matches);

                return $matches[0];
            },
            $imagePaths
        );

        return array_map(
            function ($url) use ($contents) {
                $baseUrl = $contents->getUrl()->getBaseUrl();

                return sprintf("%s/%s", $baseUrl->rtrim(), ltrim($url, "/"));
            },
            $imagePaths
        );
    }

    // Move to trait
    private function getDomCrawler(string $postHtmlContent): \Symfony\Component\DomCrawler\Crawler
    {
        $domCrawler = new DomCrawler();
        $domCrawler->addHtmlContent($postHtmlContent);

        return $domCrawler;
    }

    public function getProviderIdFromUrl(string $postUrl): string
    {
        return $this->matchPostIdFromUrl('/\/n([0-9]{6,9})-/', $postUrl);
    }
}
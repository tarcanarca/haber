<?php

namespace App\Service\Parser;

use App\Entity\PostItem;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class KibrisPostasiParser implements PostItemParser
{
    public function parsePost(string $htmlContent): PostItem
    {
        $domCrawler = new DomCrawler();
        $domCrawler->addHtmlContent($htmlContent);

        $heading   = $domCrawler->filter("#content h1.title")->text();
        $spot      = $domCrawler->filter("#content .brief")->text();
        $content   = $domCrawler->filter("#content .full")->text();
        $printLink = $domCrawler->filter(".print > a")->link();
        $imageUrls = $domCrawler->filter("#content img")->extract(["src"]);
        $createdAt = $domCrawler->filter("#main_left .int_roll_top:nth-child(2)")->text();

        $imageUrls = array_filter($imageUrls, function ($url) {
            return strstr($url, "/upload/news");
        });

        $content = preg_replace("/\sKıbrıs Postası( -{1,3} .{1,}){0,1}/", "", $content);
        $content = ltrim($content);

        $spot = ltrim($spot);

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

        $providerId = str_replace("http://www.kibrispostasi.com/print.php?news=", "", $printLink->getUri());

        //die(implode("<hr>", [$heading, $spot, $content, print_r($imageUrls, true), $createdAt->format("d/m/Y H:i"), $providerId]));

        return new PostItem(
            $providerId,
            $heading,
            $spot,
            $content,
            $createdAt
        );
    }
}
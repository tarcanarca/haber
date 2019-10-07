<?php

namespace App\Service\Parser;

use App\Entity\NewsProvider;
use App\Types\ProviderType;

class ParserFactory
{
    /**
     * @var \App\Service\Parser\KibrisPostasiParser
     */
    private $kibrisPostasiParser;

    public function __construct(KibrisPostasiParser $kibrisPostasiParser)
    {
        $this->kibrisPostasiParser = $kibrisPostasiParser;
    }

    public function getParserFor(NewsProvider $provider): PostItemParser
    {
        $parsedUrl = parse_url($provider->getUrl());
        $website   = str_replace("www.", "", $parsedUrl["host"]);

        switch ($website) {
            case "kibrispostasi.com":
                return $this->kibrisPostasiParser;
        }
    }
}
<?php

namespace App\Service\Parser;

use App\Entity\NewsProvider;

class ParserFactory
{
    /**
     * @var \App\Service\Parser\KibrisPostasiParser
     */
    private $kibrisPostasiParser;

    /**
     * @var \App\Service\Parser\GundemKibrisParser
     */
    private $gundemKibrisParser;

    public function __construct(
        KibrisPostasiParser $kibrisPostasiParser,
        GundemKibrisParser $gundemKibrisParser
    ) {
        $this->kibrisPostasiParser = $kibrisPostasiParser;
        $this->gundemKibrisParser  = $gundemKibrisParser;
    }

    public function getParserFor(NewsProvider $provider): PostItemParser
    {
        $domain = $provider->getUrl()->getDomain();

        switch ($domain) {
            case "kibrispostasi.com":
                return $this->kibrisPostasiParser;

            case "gundemkibris.com":
                return $this->gundemKibrisParser;
        }
    }
}
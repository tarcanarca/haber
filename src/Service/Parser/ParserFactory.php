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

    /**
     * @var \App\Service\Parser\DetayKibrisParser
     */
    private $detayKibrisParser;

    public function __construct(
        KibrisPostasiParser $kibrisPostasiParser,
        GundemKibrisParser $gundemKibrisParser,
        DetayKibrisParser $detayKibrisParser
    ) {
        $this->kibrisPostasiParser = $kibrisPostasiParser;
        $this->gundemKibrisParser  = $gundemKibrisParser;
        $this->detayKibrisParser   = $detayKibrisParser;
    }

    public function getParserFor(NewsProvider $provider): PostItemParser
    {
        $domain = $provider->getUrl()->getDomain();

        switch ($domain) {
            case "kibrispostasi.com":
                return $this->kibrisPostasiParser;

            case "gundemkibris.com":
                return $this->gundemKibrisParser;

            case "detaykibris.com":
                return $this->detayKibrisParser;
        }
    }
}
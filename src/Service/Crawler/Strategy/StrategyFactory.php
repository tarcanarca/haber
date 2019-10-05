<?php

namespace App\Service\Crawler\Strategy;

use App\Entity\NewsProvider;
use App\Types\ProviderType;
use Psr\Container\ContainerInterface;

class StrategyFactory
{
    /**
     * @var \App\Service\Crawler\Strategy\TeBilisimStrategy
     */
    private $teBilisimStrategy;

    /**
     * @var \App\Service\Crawler\Strategy\KibrisPostasiStrategy
     */
    private $kibrisPostasiStrategy;

    /**
     * @var \App\Service\Crawler\Strategy\CmHaberStrategy
     */
    private $cmHaberStrategy;

    public function __construct(
        CmHaberStrategy $cmHaberStrategy,
        TeBilisimStrategy $teBilisimStrategy,
        KibrisPostasiStrategy $kibrisPostasiStrategy
    ) {
        $this->cmHaberStrategy       = $cmHaberStrategy;
        $this->teBilisimStrategy     = $teBilisimStrategy;
        $this->kibrisPostasiStrategy = $kibrisPostasiStrategy;
    }

    public function getStrategyFor(NewsProvider $provider): CrawlerStrategy
    {
        switch ($provider->getType()) {
            case ProviderType::CM_HABER():
                return $this->cmHaberStrategy;

            case ProviderType::TE_BILISIM():
                return $this->teBilisimStrategy;

            case ProviderType::KIBRIS_POSTASI():
                return $this->kibrisPostasiStrategy;
        }

        throw new \InvalidArgumentException("No strategy found for provider type: " . $provider->getType());
    }
}

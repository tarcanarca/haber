<?php

namespace App\ValueObject;

use League\Url\Url as LeagueUrl;

/**
 * Wrapper around League\Url
 */
class Url
{
    /**
     * @var LeagueUrl
     */
    private $url;

    public function __construct(string $url)
    {
        $this->url = LeagueUrl::createFromUrl($url);
    }

    public function getBaseUrl(): Url
    {
        $baseUrl = sprintf("%s://%s", $this->getScheme(), $this->getHost());

        return new Url($baseUrl);
    }

    public function getScheme(): string
    {
        return (string) $this->url->getScheme();
    }

    public function getHost(): string
    {
        return (string) $this->url->getHost();
    }

    public function getPath(): string
    {
        return (string) $this->url->getPath();
    }

    public function __toString(): string
    {
        return (string) $this->url;
    }

    public function rtrim(): string
    {
        return rtrim((string) $this->url, "/");
    }

    public function ltrim(): string
    {
        return ltrim((string) $this->url, "/");
    }

    public function getDomain(): string
    {
        return str_replace("www.", "", (string) $this->getHost());
    }
}
<?php

namespace App\ValueObject;

class WebsiteContents
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var string
     */
    private $htmlContents;

    public function __construct(string $url, string $htmlContents)
    {
        $this->url          = new Url($url);
        $this->htmlContents = $htmlContents;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getHtmlContents(): string
    {
        return $this->htmlContents;
    }
}
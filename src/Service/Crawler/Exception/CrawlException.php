<?php

namespace App\Service\Crawler\Exception;

use App\Entity\NewsProvider;

class CrawlException extends \Exception
{
    public static function cannotLoadCategoryPages(NewsProvider $provider, \Throwable $previous = null): self
    {
        return new self(
            "Cannot load category pages for provider: " . $provider->getName(),
            0,
            $previous
        );
    }
}

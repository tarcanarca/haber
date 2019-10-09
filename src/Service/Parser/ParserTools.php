<?php

namespace App\Service\Parser;

use Webmozart\Assert\Assert;

trait ParserTools
{
    /**
     * @param string $regexp REGEXP to match provider ID in URL.
     * @param string $url
     *
     * @return string
     */
    protected function matchPostIdFromUrl(string $regexp, string $url): string
    {
        preg_match($regexp, $url, $matches);

        $providerId = $matches[1] ?? '';

        Assert::notEmpty($providerId, "Could not find an ID in link: " . $url);

        return $providerId;
    }
}
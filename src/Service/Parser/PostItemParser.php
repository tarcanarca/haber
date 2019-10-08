<?php

namespace App\Service\Parser;

use App\Entity\PostItem;
use App\ValueObject\WebsiteContents;

interface PostItemParser
{
    public function parsePost(WebsiteContents $contents): PostItem;

    public function getPostMainImageUrl(WebsiteContents $contents): string;

    public function getPostGalleryImageUrls(WebsiteContents $contents): array;
}
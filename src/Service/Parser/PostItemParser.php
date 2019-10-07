<?php

namespace App\Service\Parser;

use App\Entity\PostItem;

interface PostItemParser
{
    public function parsePost(string $htmlContent): PostItem;
}
<?php

namespace App\Types;

use MyCLabs\Enum\Enum;

/**
 * @method static DUNYA()
 * @method static KIBRIS()
 */
class Category extends Enum
{
    const DUNYA = "dunya-haberleri";
    const KIBRIS = "kibris-haberleri";
}

<?php

namespace App\Types;

use MyCLabs\Enum\Enum;

/**
 * @method static DUNYA()
 * @method static EGITIM()
 * @method static EKONOMI()
 * @method static KIBRIS()
 * @method static KULTUR_SANAT()
 * @method static MAGAZIN()
 * @method static RUM_BASINI()
 * @method static SAGLIK()
 * @method static SPOR()
 * @method static TURKIYE()
 */
class Category extends Enum
{
    const DUNYA = "dunya";
    const EGITIM = "egitim";
    const EKONOMI = "ekonomi";
    const KIBRIS = "kibris";
    const KULTUR_SANAT = "kultur-sanat";
    const MAGAZIN = "magazin";
    const RUM_BASINI = "rum-basini";
    const SAGLIK = "saglik";
    const SPOR = "spor";
    const TURKIYE = "turkiye";
}

<?php

namespace App\Types;

use MyCLabs\Enum\Enum;

/**
 * @method static self KIBRIS_POSTASI()
 * @method static self TE_BILISIM()
 * @method static self CM_HABER()
 */
class ProviderType extends Enum
{
    private const CM_HABER       = 'cmhaber';
    private const TE_BILISIM     = 'tebilisim';
    private const KIBRIS_POSTASI = 'kibrispostasi';
}

<?php

namespace App\Repository;

use App\Entity\NewsProvider;
use Doctrine\ORM\EntityRepository;

class RawPostRepository extends EntityRepository
{
    public function postExists(NewsProvider $provider, string $providerKey): bool
    {
        $count = $this->count([
            "provider"    => $provider,
            "providerKey" => $providerKey,
        ]);

        return $count > 0;
    }
}

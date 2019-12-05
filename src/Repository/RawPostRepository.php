<?php

namespace App\Repository;

use App\Entity\NewsProvider;
use App\Entity\RawPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RawPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, RawPost::class);
    }

    public function postExists(NewsProvider $provider, string $providerKey): bool
    {
        $count = $this->count([
            "provider"    => $provider,
            "providerKey" => $providerKey,
        ]);

        return $count > 0;
    }
}

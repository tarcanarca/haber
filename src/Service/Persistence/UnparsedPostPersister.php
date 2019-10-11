<?php

namespace App\Service\Persistence;

use App\Entity\NewsProvider;
use App\Entity\RawPost;
use App\ValueObject\WebsiteContents;
use Doctrine\ORM\EntityManagerInterface;

class UnparsedPostPersister
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(RawPost::class);
    }

    /**
     * @throws \App\Service\Persistence\DuplicateException
     */
    public function persistRawPostContents(
        NewsProvider $provider,
        WebsiteContents $contents,
        string $providerKey
    ): RawPost {
        /** @var RawPost $duplicate */
        $duplicate = $this->repository->findOneBy([
            "provider"    => $provider,
            "providerKey" => $providerKey,
        ]);

        if (null !== $duplicate) {
            throw new DuplicateException(sprintf(
                "Entity (ID: %d) exists, provider key: %s",
                $duplicate->getId(),
                $duplicate->getProviderKey()
            ));
        }

        $unparsedPost = new RawPost();

        $unparsedPost->setProvider($provider)
            ->setContents($contents->getHtmlContents())
            ->setUrl($contents->getUrl())
            ->setProviderKey($providerKey);

        $this->entityManager->persist($unparsedPost);
        $this->entityManager->flush($unparsedPost);

        return $unparsedPost;
    }
}

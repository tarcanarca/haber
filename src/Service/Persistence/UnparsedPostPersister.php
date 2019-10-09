<?php

namespace App\Service\Persistence;

use App\Entity\NewsProvider;
use App\Entity\UnparsedPost;
use App\ValueObject\WebsiteContents;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        $this->repository    = $entityManager->getRepository(UnparsedPost::class);
    }

    public function persistRawPostContents(
        NewsProvider $provider,
        WebsiteContents $contents,
        string $providerKey
    ): UnparsedPost {
        /** @var UnparsedPost $duplicate */
        $duplicate = $this->repository->findOneBy([
            "provider"    => $provider,
            "providerKey" => $providerKey,
        ]);

        if (null !== $duplicate) {
            return $duplicate;
        }

        $unparsedPost = new UnparsedPost();

        $unparsedPost->setProvider($provider)
            ->setContents($contents->getHtmlContents())
            ->setUrl($contents->getUrl())
            ->setProviderKey($providerKey);

        $this->entityManager->persist($unparsedPost);
        $this->entityManager->flush($unparsedPost);

        return $unparsedPost;
    }
}

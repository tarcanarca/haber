<?php

namespace App\Service\Persistence;

use App\Entity\NewsProvider;
use App\Entity\RawPost;
use App\Repository\RawPostRepository;
use App\Service\Persistence\Exception\DuplicateException;
use App\ValueObject\WebsiteContents;
use Doctrine\Common\Persistence\ObjectManager;

class UnparsedPostPersister
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    /**
     * @var \App\Repository\RawPostRepository
     */
    private $repository;

    public function __construct(ObjectManager $objectManager, RawPostRepository $rawPostRepository)
    {
        $this->objectManager = $objectManager;
        $this->repository    = $rawPostRepository;
    }

    /**
     * @throws \App\Service\Persistence\Exception\DuplicateException
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
            ->setProviderKey($providerKey)
            ->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->objectManager->persist($unparsedPost);
        $this->objectManager->flush();

        return $unparsedPost;
    }
}

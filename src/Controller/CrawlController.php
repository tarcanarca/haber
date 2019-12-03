<?php

namespace App\Controller;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Entity\RawPost;
use App\Service\Crawler\Crawler;
use App\Service\Parser\ParserFactory;
use App\Service\Persistence\Exception\DuplicateException;
use App\Service\Persistence\UnparsedPostPersister;
use App\Types\Category;
use App\Types\ProviderType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class CrawlController
{
    /**
     * @var \App\Service\Crawler\Crawler
     */
    private $crawler;

    /**
     * @var \App\Service\Parser\ParserFactory
     */
    private $postItemParserFactory;

    /**
     * @var \App\Service\Persistence\UnparsedPostPersister
     */
    private $unparsedPostPersister;

    /**
     * @var \App\Repository\NewsProviderRepository
     */
    private $providerRepository;

    /**
     * @var \App\Repository\RawPostRepository
     */
    private $rawPostRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Crawler $crawler,
        ParserFactory $postItemParserFactory,
        UnparsedPostPersister $unparsedPostPersister,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->crawler               = $crawler;
        $this->postItemParserFactory = $postItemParserFactory;
        $this->unparsedPostPersister = $unparsedPostPersister;
        $this->providerRepository    = $entityManager->getRepository(NewsProvider::class);
        $this->rawPostRepository     = $entityManager->getRepository(RawPost::class);
        $this->logger                = $logger;
    }

    public function index(): Response
    {
        die("Use command: bin/console a:c");

        $persistedCount = 0;

        /** @var NewsProvider $provider */
        foreach ($this->providerRepository->findAll() as $provider) {
            $parser = $this->postItemParserFactory->getParserFor($provider);

            $postLinks = $this->crawler->fetchPostLinksFromProvider($provider, $provider->getCategories());

            foreach ($postLinks as $postLink) {
                $providerPostId  = $parser->getProviderIdFromUrl($postLink);
                if ($this->rawPostRepository->postExists($provider, $providerPostId)) {
                    $this->logger->info(sprintf("Skipping post: %s as it's already saved.", $providerPostId));

                    continue;
                }

                list($websiteContents) = $this->crawler->getHtmlContentsConcurrently([$postLink]);

                try {
                    $this->unparsedPostPersister->persistRawPostContents($provider, $websiteContents, $providerPostId);
                } catch (DuplicateException $exception) {
                    continue;
                }

                $persistedCount++;
            }
        }

        return new Response("<pre>Persisted " . $persistedCount . " posts.</pre>");
    }
}
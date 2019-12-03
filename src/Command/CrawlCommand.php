<?php

namespace App\Command;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Entity\RawPost;
use App\Service\Crawler\Crawler;
use App\Service\Parser\ParserFactory;
use App\Service\Persistence\Exception\DuplicateException;
use App\Service\Persistence\UnparsedPostPersister;
use App\ValueObject\WebsiteContents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{
    protected static $defaultName = 'app:crawl';

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
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        Crawler $crawler,
        ParserFactory $postItemParserFactory,
        UnparsedPostPersister $unparsedPostPersister,
        EntityManagerInterface $entityManager
    ) {
        $this->crawler               = $crawler;
        $this->postItemParserFactory = $postItemParserFactory;
        $this->unparsedPostPersister = $unparsedPostPersister;

        // configure service factory methods for these in services.yaml
        $this->providerRepository    = $entityManager->getRepository(NewsProvider::class);
        $this->rawPostRepository     = $entityManager->getRepository(RawPost::class);

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $this->io = new SymfonyStyle($input, $output);
        $this->io->title((new \DateTime())->format('Y-m-d H:i:s'));

        /** @var NewsProvider $provider */
        foreach ($this->providerRepository->findAll() as $provider) {
            $parser     = $this->postItemParserFactory->getParserFor($provider);
            $categories = $provider->getCategories();

            $this->io->section(sprintf(
                "Scanning new posts in %d categories from: %s",
                $categories->count(),
                $provider->getName()
            ));

            $postLinks = $this->fetchNewPostsFrom($provider, $categories);

            if (empty($postLinks)) {
                $this->io->text("No new posts found");

                continue;
            }

            $this->io->writeln(sprintf("Fetching %d new posts...", count($postLinks)));
            $this->io->progressStart(count($postLinks));

            $persistedCount = 0;
            foreach ($this->crawler->getHtmlContentsConcurrently($postLinks) as $websiteContents) {
                $providerPostId = $parser->getProviderIdFromUrl($websiteContents->getUrl());

                try {
                    $this->unparsedPostPersister->persistRawPostContents($provider, $websiteContents, $providerPostId);
                } catch (DuplicateException $exception) {
                    $this->io->error("Attempted to save a duplicate post: " . $exception->getMessage());

                    continue;
                }

                $this->io->progressAdvance();
                $persistedCount++;
            }

            $this->io->progressFinish();

            $this->io->success(sprintf("Saved %d posts", $persistedCount));
        }

        $duration = microtime(true) - $start;
        $this->io->writeln(sprintf("Crawl completed in %.2f seconds", $duration));
    }

    /**
     * @param NewsProvider           $provider
     * @param NewsProviderCategory[] $categories
     *
     * @return WebsiteContents[]
     */
    private function fetchNewPostsFrom(NewsProvider $provider, iterable $categories): array
    {
        $parser    = $this->postItemParserFactory->getParserFor($provider);
        $postLinks = $this->crawler->fetchPostLinksFromProvider($provider, $categories);

        $this->io->writeln(sprintf("Fetched links from %s: %s", $provider, print_r($postLinks, true)), SymfonyStyle::VERBOSITY_DEBUG);

        $postLinks = array_filter(
            $postLinks,
            function ($postLink) use ($parser, $provider) {
                $providerPostId = $parser->getProviderIdFromUrl($postLink);

                $postIsNew = (false === $this->rawPostRepository->postExists($provider, $providerPostId));

                if (!$postIsNew) {
                    $this->io->writeln(sprintf("Ignoring existing post: %s (of: %s)", $providerPostId, $provider), SymfonyStyle::VERBOSITY_DEBUG);
                }

                return $postIsNew;
            }
        );

        return $postLinks;
    }
}

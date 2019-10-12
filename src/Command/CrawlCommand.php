<?php

namespace App\Command;

use App\Entity\NewsProvider;
use App\Entity\RawPost;
use App\Service\Crawler\WebsiteCrawler;
use App\Service\Parser\ParserFactory;
use App\Service\Persistence\DuplicateException;
use App\Service\Persistence\UnparsedPostPersister;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{
    protected static $defaultName = 'app:crawl';

    /**
     * @var \App\Service\Crawler\WebsiteCrawler
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

    public function __construct(
        WebsiteCrawler $crawler,
        ParserFactory $postItemParserFactory,
        UnparsedPostPersister $unparsedPostPersister,
        EntityManagerInterface $entityManager
    ) {
        $this->crawler               = $crawler;
        $this->postItemParserFactory = $postItemParserFactory;
        $this->unparsedPostPersister = $unparsedPostPersister;
        $this->providerRepository    = $entityManager->getRepository(NewsProvider::class);
        $this->rawPostRepository     = $entityManager->getRepository(RawPost::class);

        parent::__construct();
    }

    protected function configure()
    {
        //
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $io = new SymfonyStyle($input, $output);
        $io->title((new \DateTime())->format('Y-m-d H:i:s'));

        /** @var NewsProvider $provider */
        foreach ($this->providerRepository->findAll() as $provider) {
            $categories = $provider->getCategories();
            $io->section(sprintf(
                "Scanning new posts in %d categories from: %s",
                $categories->count(),
                $provider->getName()
            ));

            $parser    = $this->postItemParserFactory->getParserFor($provider);
            $postLinks = $this->crawler->fetchPostLinksFromProvider($provider, $categories);

            $postLinks = array_filter($postLinks, function ($postLink) use ($parser, $provider) {
                $providerPostId = $parser->getProviderIdFromUrl($postLink);

                return false ===$this->rawPostRepository->postExists($provider, $providerPostId);
            });

            if (empty($postLinks)) {
                $io->text("No new posts found");

                continue;
            }

            $io->writeln(sprintf("Fetching %d new posts...", count($postLinks)));
            $io->progressStart(count($postLinks));

            $persistedCount = 0;
            foreach ($postLinks as $postLink) {
                $providerPostId  = $parser->getProviderIdFromUrl($postLink);
                $websiteContents = $this->crawler->getHtmlContents($postLink);

                try {
                    $this->unparsedPostPersister->persistRawPostContents($provider, $websiteContents, $providerPostId);
                } catch (DuplicateException $exception) {
                    $io->error("Attempted to save a duplicate post: " . $exception->getMessage());

                    continue;
                }

                $io->progressAdvance();
                $persistedCount++;
            }

            $io->progressFinish();

            $io->success(sprintf("Saved %d posts", $persistedCount));
        }

        $duration = microtime(true) - $start;
        $io->writeln(sprintf("Crawl completed in %.2f seconds", $duration));
    }
}

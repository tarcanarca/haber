<?php

namespace App\Controller;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Service\Crawler\WebsiteCrawler;
use App\Service\Parser\ParserFactory;
use App\Service\Persistence\DuplicateException;
use App\Service\Persistence\UnparsedPostPersister;
use App\Types\Category;
use App\Types\ProviderType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CrawlController
{
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
    }

    public function index(): Response
    {
        $postLinks = [];
        $i = 0;
        $persistedCount = 0;

        /** @var NewsProvider $provider */
        foreach ($this->providerRepository->findAll() as $provider) {
            $parser = $this->postItemParserFactory->getParserFor($provider);

            $postLinks = array_merge(
                $postLinks,
                $this->crawler->fetchPostLinksFromProvider($provider)
            );

            foreach ($postLinks as $postLink) {
                $websiteContents = $this->crawler->getHtmlContents($postLink);
                $providerPostId  = $parser->getProviderIdForPost($websiteContents);

                try {
//                    $this->unparsedPostPersister->persistRawPostContents($provider, $websiteContents, $providerPostId);
                } catch (DuplicateException $exception) {
                    continue;
                }

                $persistedCount++;

                $postItem = $parser->parsePost($websiteContents);
                $postItem->setProviderId($providerPostId);
                $postItem->setImages(
                    array_merge([$parser->getPostMainImageUrl($websiteContents)],
                    $parser->getPostGalleryImageUrls($websiteContents))
                );
                $postItems[] = $postItem;

                //$this->postRepository->persist($postItem);

                // stop after 1
                if ($i++ > 4) {
                    break 2;
                }
            }
        }

//        return new Response("<pre>Persisted " . $persistedCount . " posts.</pre>");
        return new Response("<pre>" . print_r($postItems, true) . "</pre>");
    }

    /**
     * @return \App\Entity\NewsProvider[]
     */
    private function getProvidersToCrawl(): array
    {
        return [
            (new NewsProvider(ProviderType::KIBRIS_POSTASI(), "Kibris Postasi", "http://www.kibrispostasi.com"))
                ->setCategories(new ArrayCollection([
                    new NewsProviderCategory(Category::KIBRIS(), "c35-KIBRIS_HABERLERI"),
                ])),
            (new NewsProvider(ProviderType::TE_BILISIM(), "Gundem Kibris", "http://www.gundemkibris.com"))
                ->setCategories(new ArrayCollection([
                    new NewsProviderCategory(Category::KIBRIS(), "kibris"),
                    new NewsProviderCategory(Category::DUNYA(), "dunya"),
                ])),
            (new NewsProvider(ProviderType::CM_HABER(), "Detay Kibris", "http://www.detaykibris.com"))
                ->setCategories(new ArrayCollection([
                    new NewsProviderCategory(Category::DUNYA(), "dunya-haberleri-45hk.htm"),
                    new NewsProviderCategory(Category::KIBRIS(), "kibris-haberleri-7hk.htm"),
                ])),
        ];
    }
}
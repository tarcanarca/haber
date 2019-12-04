<?php

namespace App\Tests;

use App\DataFixtures\ProviderFixture;
use App\Entity\NewsProvider;
use App\Service\Crawler\AsyncCrawler;
use Http\Adapter\Guzzle6\Client;
use Http\Client\HttpAsyncClient;
use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Service\Crawler\Strategy\CmHaberStrategy
 * @covers \App\Service\Crawler\AsyncCrawler
 */
class AsyncCrawlerTest extends KernelTestCase
{

    /**
     * @var AsyncCrawler
     */
    private $crawler;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|HttpAsyncClient
     */
    private $httpClientMock;

    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();

        $this->objectManager = self::$container->get('doctrine')->getManager();

        $this->httpClientMock = $this->createMock(HttpAsyncClient::class);

        self::$container->set(Client::class, $this->httpClientMock);
        $this->crawler = self::$container->get(AsyncCrawler::class);
    }

    /**
     * @testdox Crawler can fetch post links for a given provider
     */
    public function testPostLinksCanBeFetched()
    {
        /** @var NewsProvider $provider */
        $provider = $this->objectManager->getRepository(NewsProvider::class)->findOneBy(['name' => ProviderFixture::TEST_PROVIDER_1]);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn("
                <a href='{$provider->getUrl()}/blabla-123456h.htm'>Post link 1</a>
                <a href='{$provider->getUrl()}/second-post-123457h.htm'>Post link 2</a>
            ");

        $promise1 = $this->createMock(Promise::class);
        $promise1->expects($this->once())
            ->method('wait')
            ->willReturn($responseMock);

        $this->httpClientMock->expects($this->once())
            ->method('sendAsyncRequest')
            ->willReturn($promise1);

        $links = $this->crawler->fetchPostLinksFromProvider($provider, [$provider->getCategories()->first()]);

        self::assertCount(2, $links);
    }

    /**
     * @testdox Exception is thrown if http client fails on any of concurrent requests.
     *
     * @expectedException \App\Service\Crawler\Exception\CrawlException
     */
    public function testFetchingLinksFailsOnClientError()
    {
        /** @var NewsProvider $provider */
        $provider = $this->objectManager->getRepository(NewsProvider::class)->findOneBy(['name' => ProviderFixture::TEST_PROVIDER_1]);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->never())
            ->method('getBody');

        $promise1 = $this->createMock(Promise::class);
        $promise1->expects($this->at(1))
            ->method('wait')
            ->willThrowException(new \Exception("Connection failure"));

        $this->httpClientMock->expects($this->exactly(2))
            ->method('sendAsyncRequest')
            ->willReturn($promise1);

        $links = $this->crawler->fetchPostLinksFromProvider($provider, $provider->getCategories()->slice(0, 2));

        self::assertCount(2, $links);
    }

    /**
     * @todo
     */
    public function XtestPostContentsAreTrimmed()
    {

    }

    public function testExternalLinksAreNotFetched()
    {
        /** @var NewsProvider $provider */
        $provider = $this->objectManager->getRepository(NewsProvider::class)->findOneBy(['name' => ProviderFixture::TEST_PROVIDER_1]);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn("
                <a href='{$provider->getUrl()}/blabla-123456h.htm'>Post link 1</a>
                <a href='http://google.com'>External Link</a>
            ");

        $promise1 = $this->createMock(Promise::class);
        $promise1->expects($this->once())
            ->method('wait')
            ->willReturn($responseMock);

        $this->httpClientMock->expects($this->once())
            ->method('sendAsyncRequest')
            ->willReturn($promise1);

        $links = $this->crawler->fetchPostLinksFromProvider($provider, [$provider->getCategories()->first()]);

        self::assertCount(1, $links);
    }
}

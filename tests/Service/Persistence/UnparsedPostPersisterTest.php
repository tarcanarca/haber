<?php


namespace App\Tests\Service\Persistence;


use App\DataFixtures\ProviderFixture;
use App\Entity\NewsProvider;
use App\Entity\RawPost;
use App\Service\Persistence\UnparsedPostPersister;
use App\ValueObject\WebsiteContents;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnparsedPostPersisterTest extends WebTestCase
{
    /**
     * @var UnparsedPostPersister
     */
    private $persister;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $container = self::$container;

        // @todo: Create mechanism to mock autowired dependencies automatically.

        $this->objectManager = $container->get('doctrine')->getManager();
        $this->persister = $container->get(UnparsedPostPersister::class);
    }

    public function testRawPostContentsArePersisted()
    {
        $this->createAndSaveNewEntity(new WebsiteContents('http://xxx', 'abc'), "post123");

        $repo = $this->objectManager->getRepository(RawPost::class);

        /** @var RawPost $post */
        $post = $repo->findOneBy([]);

        self::assertInstanceOf(RawPost::class, $post);
        self::assertSame("post123", $post->getProviderKey());
    }

    private function createAndSaveNewEntity(WebsiteContents $contents, string $providerKey)
    {
        /** @var NewsProvider $provider */
        $provider = $this->objectManager->getRepository(NewsProvider::class)->findOneBy(['name' => ProviderFixture::TEST_PROVIDER_1]);
        $this->objectManager->persist($provider);

        $this->persister->persistRawPostContents($provider, $contents, $providerKey);
    }

    /**
     * @expectedException \App\Service\Persistence\Exception\DuplicateException
     */
    public function testExceptionIsThrownOnDuplicate()
    {
        $this->createAndSaveNewEntity(new WebsiteContents('http://xxx', 'abc'), "post123");
        $this->createAndSaveNewEntity(new WebsiteContents('http://xxx', 'abc22'), "post002");
        $this->createAndSaveNewEntity(new WebsiteContents('http://xxx', 'Some other'), "post123");
    }
}
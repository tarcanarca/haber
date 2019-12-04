<?php


namespace App\Tests\Service\Persistence;


use App\Entity\NewsProvider;
use App\Entity\RawPost;
use App\Repository\RawPostRepository;
use App\Service\Persistence\UnparsedPostPersister;
use App\ValueObject\WebsiteContents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnparsedPostPersisterTest extends WebTestCase
{
    /**
     * @var UnparsedPostPersister
     */
    private $persister;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    private $entityManagerMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RawPostRepository
     */
    private $repositoryMock;

    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $container = self::$container;

        // @todo: Create mechanism to mock autowired dependencies automatically.
        $this->entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this->getMockBuilder(RawPostRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(RawPost::class)
            ->willReturn($this->repositoryMock);

        $this->persister = new UnparsedPostPersister($this->entityManagerMock);
    }

    public function testRawPostContentsArePersisted()
    {
        $this->persister->persistRawPostContents(
            new NewsProvider(),
            $contents = new WebsiteContents(),
            "post123"
        );
    }
}
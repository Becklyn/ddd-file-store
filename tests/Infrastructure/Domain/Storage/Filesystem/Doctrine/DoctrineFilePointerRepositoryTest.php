<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerId;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use Becklyn\Ddd\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine\DoctrineFilePointerRepository;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-30
 *
 * @covers \Becklyn\Ddd\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine\DoctrineFilePointerRepository
 */
class DoctrineFilePointerRepositoryTest extends TestCase
{
    use ProphecyTrait;
    use FileTestTrait;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private ObjectProphecy $em;

    /**
     * @var ObjectProphecy|ObjectRepository
     */
    private ObjectProphecy $repository;

    private DoctrineFilePointerRepository $fixture;

    protected function setUp() : void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(ObjectRepository::class);
        $this->em->getRepository(FilePointer::class)->willReturn($this->repository->reveal());
        $this->fixture = new DoctrineFilePointerRepository($this->em->reveal());
    }

    public function testNextIdentityReturnsFilePointerId() : void
    {
        self::assertInstanceOf(FilePointerId::class, $this->fixture->nextIdentity());
    }

    public function testAddPersistsFileToEntityManager() : void
    {
        $filePointer = $this->givenAFilePointer();
        $this->fixture->add($filePointer);
        $this->em->persist($filePointer)->shouldHaveBeenCalled();
    }

    private function givenAFilePointer() : FilePointer
    {
        return FilePointer::create(FilePointerId::next(), $this->givenAFileId(), \uniqid());
    }

    public function testFindOneByFileIdReturnsFileFoundByDoctrineRepository() : void
    {
        $filePointer = $this->givenAFilePointer();
        $this->repository->findOneBy(['fileId' => $filePointer->fileId()->asString()])->willReturn($filePointer);
        self::assertSame($filePointer, $this->fixture->findOneByFileId($filePointer->fileId()));
    }

    public function testFindOneByIdThrowsFilePointerNotFoundExceptionIfDoctrineRepositoryReturnsNull() : void
    {
        $fileId = $this->givenAFileId();
        $this->repository->findOneBy(['fileId' => $fileId->asString()])->willReturn(null);
        $this->expectException(FilePointerNotFoundException::class);
        $this->fixture->findOneByFileId($fileId);
    }

    public function testRemoveRemovesFileFromEntityManagerAndCallsDeleteOnFilePointer() : void
    {
        $filePointer = $this->prophesize(FilePointer::class);
        $this->fixture->remove($filePointer->reveal());
        $this->em->remove($filePointer)->shouldHaveBeenCalled();
        $filePointer->delete()->shouldHaveBeenCalled();
    }
}

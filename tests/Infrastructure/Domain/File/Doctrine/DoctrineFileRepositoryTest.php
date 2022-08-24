<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Infrastructure\Domain\File\Doctrine;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileNotFoundException;
use Becklyn\Ddd\FileStore\Infrastructure\Domain\File\Doctrine\DoctrineFileRepository;
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
 * @covers \Becklyn\Ddd\FileStore\Infrastructure\Domain\File\Doctrine\DoctrineFileRepository
 */
class DoctrineFileRepositoryTest extends TestCase
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

    private DoctrineFileRepository $fixture;

    protected function setUp() : void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(ObjectRepository::class);
        $this->em->getRepository(File::class)->willReturn($this->repository->reveal());
        $this->fixture = new DoctrineFileRepository($this->em->reveal());
    }

    public function testNextIdentityReturnsFileId() : void
    {
        self::assertInstanceOf(FileId::class, $this->fixture->nextIdentity());
    }

    public function testAddPersistsFileToEntityManager() : void
    {
        $file = $this->givenAFile();
        $this->fixture->add($file);
        $this->em->persist($file)->shouldHaveBeenCalled();
    }

    private function givenAFile() : File
    {
        return File::create($this->givenAFileId(), $this->givenAFilename(), $this->givenFileContents());
    }

    public function testFindOneByIdReturnsFileFoundByDoctrineRepository() : void
    {
        $file = $this->givenAFile();
        $this->repository->findOneBy(['id' => $file->id()->asString()])->willReturn($file);
        self::assertSame($file, $this->fixture->findOneById($file->id()));
    }

    public function testFindOneByIdThrowsFileNotFoundExceptionIfDoctrineRepositoryReturnsNull() : void
    {
        $fileId = $this->givenAFileId();
        $this->repository->findOneBy(['id' => $fileId->asString()])->willReturn(null);
        $this->expectException(FileNotFoundException::class);
        $this->fixture->findOneById($fileId);
    }

    public function testRemoveRemovesFileFromEntityManagerAndCallsDeleteOnFile() : void
    {
        $file = $this->prophesize(File::class);
        $this->fixture->remove($file->reveal());
        $this->em->remove($file)->shouldHaveBeenCalled();
        $file->delete()->shouldHaveBeenCalled();
    }

    public function testFindOneByOwnerIdReturnsFileFoundByDoctrineRepository() : void
    {
        $file = $this->givenAFile();
        $ownerId = TestProxyOwnerId::next();
        $this->repository->findOneBy(['ownerId' => $ownerId->asString(), 'ownerType' => $ownerId->aggregateType()])->willReturn($file);
        self::assertSame($file, $this->fixture->findOneByOwnerId($ownerId));
    }

    public function testFindOneByOwnerIdThrowsFileNotFoundExceptionIfDoctrineRepositoryReturnsNull() : void
    {
        $ownerId = TestProxyOwnerId::next();
        $this->repository->findOneBy(['ownerId' => $ownerId->asString(), 'ownerType' => $ownerId->aggregateType()])->willReturn(null);
        $this->expectException(FileNotFoundException::class);
        $this->fixture->findOneByOwnerId($ownerId);
    }
}

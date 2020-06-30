<?php

namespace C201\FileStore\Tests\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use C201\Ddd\Events\Domain\DomainEventTestTrait;
use C201\FileStore\Domain\FileTestTrait;
use C201\FileStore\Domain\Storage\Filesystem\FilePointer;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerDeleted;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use C201\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine\DoctrineFilePointerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-30
 *
 * @covers \C201\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine\DoctrineFilePointerRepository
 */
class DoctrineFilePointerRepositoryTest extends TestCase
{
    use ProphecyTrait;
    use DomainEventTestTrait;
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

    protected function setUp(): void
    {
        $this->initDomainEventTestTrait();
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(ObjectRepository::class);
        $this->em->getRepository(FilePointer::class)->willReturn($this->repository->reveal());
        $this->fixture = new DoctrineFilePointerRepository($this->em->reveal(), $this->eventRegistry->reveal());
    }

    public function testNextIdentityReturnsFilePointerId(): void
    {
        $this->assertInstanceOf(FilePointerId::class, $this->fixture->nextIdentity());
    }

    public function testAddPersistsFileToEntityManager(): void
    {
        $filePointer = $this->givenAFilePointer();
        $this->fixture->add($filePointer);
        $this->em->persist($filePointer)->shouldHaveBeenCalled();
    }

    private function givenAFilePointer(): FilePointer
    {
        return FilePointer::create(FilePointerId::next(), $this->givenAFileId(), uniqid());
    }

    public function testFindOneByFileIdReturnsFileFoundByDoctrineRepository(): void
    {
        $filePointer = $this->givenAFilePointer();
        $this->repository->findOneBy(['fileId' => $filePointer->fileId()->asString()])->willReturn($filePointer);
        $this->assertSame($filePointer, $this->fixture->findOneByFileId($filePointer->fileId()));
    }

    public function testFindOneByIdThrowsFilePointerNotFoundExceptionIfDoctrineRepositoryReturnsNull(): void
    {
        $fileId = $this->givenAFileId();
        $this->repository->findOneBy(['fileId' => $fileId->asString()])->willReturn(null);
        $this->expectException(FilePointerNotFoundException::class);
        $this->fixture->findOneByFileId($fileId);
    }

    public function testRemoveRemovesFileFromEntityManagerAndRegisterAFileDeletedEvent(): void
    {
        $filePointer = $this->givenAFilePointer();
        $this->fixture->remove($filePointer);
        $this->em->remove($filePointer)->shouldHaveBeenCalled();
        $this->eventRegistry->registerEvent(Argument::that(fn(FilePointerDeleted $event) => $event->aggregateId()->equals($filePointer->id())))->shouldHaveBeenCalled();
    }
}

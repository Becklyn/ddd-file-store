<?php

namespace C201\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use Becklyn\Ddd\Events\Domain\EventRegistry;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointer;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerDeleted;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerRepository;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
class DoctrineFilePointerRepository implements FilePointerRepository
{
    use EventCreatorCapabilities;

    private EntityManagerInterface $em;
    private ObjectRepository $repository;
    private EventRegistry $eventRegistry;

    public function __construct(EntityManagerInterface $em, EventRegistry $eventRegistry)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(FilePointer::class);
        $this->eventRegistry = $eventRegistry;
    }

    public function nextIdentity(): FilePointerId
    {
        return FilePointerId::next();
    }

    public function add(FilePointer $filePointer): void
    {
        $this->em->persist($filePointer);
    }

    public function findOneByFileId(FileId $fileId): FilePointer
    {
        /** @var FilePointer $filePointer */
        $filePointer = $this->repository->findOneBy(['fileId' => $fileId->asString()]);

        if ($filePointer === null) {
            throw new FilePointerNotFoundException("File pointer for file '{$fileId->asString()}' could not be found");
        }

        return $filePointer;
    }

    public function remove(FilePointer $filePointer): void
    {
        $this->em->remove($filePointer);
        $this->eventRegistry->registerEvent(new FilePointerDeleted($this->nextEventIdentity(), new \DateTimeImmutable(), $filePointer->id()));
    }
}

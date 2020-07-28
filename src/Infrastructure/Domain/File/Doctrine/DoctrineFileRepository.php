<?php

namespace C201\FileStore\Infrastructure\Domain\File\Doctrine;

use C201\Ddd\Events\Domain\EventCreatorCapabilities;
use C201\Ddd\Events\Domain\EventRegistry;
use C201\Ddd\Identity\Domain\AggregateId;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileDeleted;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\File\FileRepository;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 */
class DoctrineFileRepository implements FileRepository
{
    use EventCreatorCapabilities;

    private EntityManagerInterface $em;
    private ObjectRepository $repository;
    private EventRegistry $eventRegistry;

    public function __construct(EntityManagerInterface $em, EventRegistry $eventRegistry)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(File::class);
        $this->eventRegistry = $eventRegistry;
    }

    public function nextIdentity(): FileId
    {
        return FileId::next();
    }

    public function add(File $file): void
    {
        $this->em->persist($file);
    }

    public function findOneById(FileId $fileId): File
    {
        /** @var File $file */
        $file = $this->repository->findOneBy(['id' => $fileId->asString()]);

        if ($file === null) {
            throw new FileNotFoundException("File with id '{$fileId->asString()}' could not be found");
        }

        return $file;
    }

    public function remove(File $file): void
    {
        $this->em->remove($file);
        $this->eventRegistry->registerEvent(new FileDeleted($this->nextEventIdentity(), new \DateTimeImmutable(), $file->id()));
    }

    public function findOneByOwnerId(AggregateId $ownerId): File
    {
        /** @var File $file */
        $file = $this->repository->findOneBy(['ownerId' => $ownerId->asString(), 'ownerType' => $ownerId->aggregateType()]);

        if ($file === null) {
            throw new FileNotFoundException("File with owner '{$ownerId->asString()}' of type '{$ownerId->aggregateType()}' could not be found");
        }

        return $file;
    }
}

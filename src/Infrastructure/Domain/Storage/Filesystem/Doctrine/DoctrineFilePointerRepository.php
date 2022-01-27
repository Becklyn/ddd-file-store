<?php declare(strict_types=1);

namespace Becklyn\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use Becklyn\Ddd\Events\Domain\EventRegistry;
use Becklyn\FileStore\Domain\File\FileId;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerDeleted;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerId;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
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

    public function nextIdentity() : FilePointerId
    {
        return FilePointerId::next();
    }

    public function add(FilePointer $filePointer) : void
    {
        $this->em->persist($filePointer);
    }

    public function findOneByFileId(FileId $fileId) : FilePointer
    {
        /** @var ?FilePointer $filePointer */
        $filePointer = $this->repository->findOneBy(['fileId' => $fileId->asString()]);

        if (null === $filePointer) {
            throw new FilePointerNotFoundException("File pointer for file '{$fileId->asString()}' could not be found");
        }

        return $filePointer;
    }

    public function remove(FilePointer $filePointer) : void
    {
        $this->em->remove($filePointer);
        $this->eventRegistry->registerEvent(new FilePointerDeleted($this->nextEventIdentity(), new \DateTimeImmutable(), $filePointer->id()));
    }
}

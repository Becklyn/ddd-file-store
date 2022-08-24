<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerId;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerRepository;
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

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(FilePointer::class);
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

        $filePointer->delete();
    }
}

<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Infrastructure\Domain\File\Doctrine;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileNotFoundException;
use Becklyn\Ddd\FileStore\Domain\File\FileRepository;
use Becklyn\Ddd\Identity\Domain\AggregateId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-26
 */
class DoctrineFileRepository implements FileRepository
{
    use EventCreatorCapabilities;

    private EntityManagerInterface $em;
    private ObjectRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(File::class);
    }

    public function nextIdentity() : FileId
    {
        return FileId::next();
    }

    public function add(File $file) : void
    {
        $this->em->persist($file);
    }

    public function findOneById(FileId $fileId) : File
    {
        /** @var ?File $file */
        $file = $this->repository->findOneBy(['id' => $fileId->asString()]);

        if (null === $file) {
            throw new FileNotFoundException("File with id '{$fileId->asString()}' could not be found");
        }

        return $file;
    }

    public function remove(File $file) : void
    {
        $this->em->remove($file);

        $file->delete();
    }

    public function findOneByOwnerId(AggregateId $ownerId) : File
    {
        /** @var ?File $file */
        $file = $this->repository->findOneBy(['ownerId' => $ownerId->asString(), 'ownerType' => $ownerId->aggregateType()]);

        if (null === $file) {
            throw new FileNotFoundException("File with owner '{$ownerId->asString()}' of type '{$ownerId->aggregateType()}' could not be found");
        }

        return $file;
    }
}

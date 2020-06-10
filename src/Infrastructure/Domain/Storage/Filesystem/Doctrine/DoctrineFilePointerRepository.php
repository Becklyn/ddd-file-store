<?php

namespace C201\FileStore\Infrastructure\Domain\Storage\Filesystem\Doctrine;

use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointer;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
class DoctrineFilePointerRepository implements FilePointerRepository
{
    private EntityManagerInterface $em;
    private ObjectRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(FilePointer::class);
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
}

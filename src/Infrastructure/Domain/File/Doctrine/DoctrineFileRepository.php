<?php

namespace C201\FileStore\Infrastructure\Domain\File\Doctrine;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\File\FileRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 */
class DoctrineFileRepository implements FileRepository
{
    private EntityManagerInterface $em;
    private ObjectRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(File::class);
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
}

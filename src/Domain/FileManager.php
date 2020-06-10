<?php

namespace C201\FileStore\Domain;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\File\FileRepository;
use C201\FileStore\Domain\Storage\FileNotFoundInStorageException;
use C201\FileStore\Domain\Storage\FileNotStoredException;
use C201\FileStore\Domain\Storage\Storage;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 */
class FileManager
{
    private FileRepository $fileRepository;
    private Storage $storage;

    public function __construct(FileRepository $fileRepository, Storage $storage)
    {
        $this->fileRepository = $fileRepository;
        $this->storage = $storage;
    }

    /**
     * @throws FileNotStoredException
     */
    public function new(string $filename, string $contents): File
    {
        $file = File::create($this->fileRepository->nextIdentity(), $filename, $contents);
        $this->storage->storeFileContents($file);
        $this->fileRepository->add($file);
        return $file;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotFoundInStorageException
     */
    public function load(FileId $fileId): File
    {
        $file = $this->fileRepository->findOneById($fileId);
        $file->load($this->storage->loadFileContents($file));
        return $file;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotStoredException
     */
    public function replaceContents(FileId $fileId, string $newContents): File
    {
        $file = $this->fileRepository->findOneById($fileId);
        $file->updateContents($newContents);
        $this->storage->storeFileContents($file);
        return $file;
    }
}

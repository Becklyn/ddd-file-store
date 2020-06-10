<?php

namespace C201\FileStore\Domain\Storage\Filesystem;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\Storage\FileNotFoundInStorageException;
use C201\FileStore\Domain\Storage\FileNotStoredException;
use C201\FileStore\Domain\Storage\Storage;
use C201\Ddd\Events\Domain\EventRegistry;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
class FilesystemStorage implements Storage
{
    private EventRegistry $eventRegistry;
    private FilePointerRepository $filePointerRepository;
    private PathGenerator $pathGenerator;
    private Filesystem $filesystem;

    public function __construct(EventRegistry $eventRegistry, FilePointerRepository $filePointerRepository, PathGenerator $pathGenerator, Filesystem $filesystem)
    {
        $this->eventRegistry = $eventRegistry;
        $this->filePointerRepository = $filePointerRepository;
        $this->pathGenerator = $pathGenerator;
        $this->filesystem = $filesystem;
    }

    public function storeFileContents(File $file): void
    {
        try {
            $filePointer = $this->filePointerRepository->findOneByFileId($file->id());
        } catch (FilePointerNotFoundException $e) {
            $path = $this->pathGenerator->generate($file->filename());
            $filePointer = FilePointer::create($this->filePointerRepository->nextIdentity(), $file->id(), $path);
            $this->filePointerRepository->add($filePointer);
            $this->eventRegistry->dequeueProviderAndRegister($filePointer);
        }

        try {
            $this->filesystem->dumpFile($filePointer->path(), $file->contents());
        } catch (\Exception $e) {
            throw new FileNotStoredException("Contents for file '{$file->id()->asString()}' could not be written to filesystem storage relative path '{$filePointer->path()}'");
        }
    }

    public function loadFileContents(File $file): string
    {
        try {
            $filePointer = $this->filePointerRepository->findOneByFileId($file->id());
        } catch (FilePointerNotFoundException $e) {
            throw new FileNotFoundInStorageException("The contents of file '{$file->id()->asString()}' could not be found in storage", 0, $e);
        }

        try {
            return $this->filesystem->readFile($filePointer->path());
        } catch (FileNotFoundInFilesystemException $e) {
            throw new FileNotFoundInStorageException("The contents of file '{$file->id()->asString()}' could not be found in storage", 0, $e);
        }
    }
}

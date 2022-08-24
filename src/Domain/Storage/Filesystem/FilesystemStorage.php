<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Domain\EventRegistry;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotFoundInStorageException;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotStoredException;
use Becklyn\Ddd\FileStore\Domain\Storage\Storage;
use Becklyn\Ddd\Messages\Domain\Message;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
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

    public function storeFileContents(File $file, Message $trigger) : void
    {
        try {
            $filePointer = $this->filePointerRepository->findOneByFileId($file->id());
        } catch (FilePointerNotFoundException $e) {
            $path = $this->pathGenerator->generate($file->filename());
            $filePointer = FilePointer::create($this->filePointerRepository->nextIdentity(), $file->id(), $path);
            $this->filePointerRepository->add($filePointer);
            $this->eventRegistry->dequeueProviderAndRegister($filePointer, $trigger);
        }

        try {
            $this->filesystem->dumpFile($filePointer->path(), $file->contents());
        } catch (\Exception $e) {
            throw new FileNotStoredException("Contents for file '{$file->id()->asString()}' could not be written to filesystem storage relative path '{$filePointer->path()}'");
        }
    }

    public function loadFileContents(File $file) : string
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

    public function deleteFileContents(File $file, Message $trigger) : void
    {
        try {
            $filePointer = $this->filePointerRepository->findOneByFileId($file->id());
        } catch (FilePointerNotFoundException $e) {
            return;
        }

        $this->filePointerRepository->remove($filePointer);
        $this->filesystem->remove($filePointer->path());

        $this->eventRegistry->dequeueProviderAndRegister($filePointer, $trigger);
    }
}

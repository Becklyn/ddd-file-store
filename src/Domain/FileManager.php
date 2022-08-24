<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileNotFoundException;
use Becklyn\Ddd\FileStore\Domain\File\FileRepository;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotFoundInStorageException;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotStoredException;
use Becklyn\Ddd\FileStore\Domain\Storage\Storage;
use Becklyn\Ddd\Messages\Domain\Message;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
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
    public function new(string $filename, string $contents, Message $trigger) : File
    {
        $file = File::create($this->fileRepository->nextIdentity(), $filename, $contents);
        $this->storage->storeFileContents($file, $trigger);
        $this->fileRepository->add($file);
        return $file;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotFoundInStorageException
     */
    public function load(FileId $fileId) : File
    {
        $file = $this->fileRepository->findOneById($fileId);
        $file->load($this->storage->loadFileContents($file));
        return $file;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotStoredException
     */
    public function replaceContents(FileId $fileId, string $newContents, Message $trigger) : File
    {
        $file = $this->fileRepository->findOneById($fileId);

        $originalHash = $file->contentHash();
        $file->updateContents($newContents);

        if ($originalHash === $file->contentHash()) {
            return $file;
        }

        $this->storage->storeFileContents($file, $trigger);
        return $file;
    }

    /**
     * @throws FileNotFoundException
     */
    public function delete(FileId $fileId, Message $trigger) : File
    {
        $file = $this->fileRepository->findOneById($fileId);

        $this->fileRepository->remove($file);
        $this->storage->deleteFileContents($file, $trigger);

        return $file;
    }
}

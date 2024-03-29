<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Testing;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileNotFoundException;
use Becklyn\Ddd\FileStore\Domain\File\FileRepository;
use Becklyn\Ddd\FileStore\Domain\FileManager;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotFoundInStorageException;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotStoredException;
use Becklyn\Ddd\Messages\Domain\Message;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-03
 *
 * @codeCoverageIgnore
 */
trait FileTestTrait
{
    /** @var ObjectProphecy|FileManager */
    protected ObjectProphecy $fileManager;
    /** @var ObjectProphecy|FileRepository */
    protected ObjectProphecy $fileRepository;

    protected function initFilesTestTrait() : void
    {
        $this->fileManager = $this->prophesize(FileManager::class);
        $this->fileRepository = $this->prophesize(FileRepository::class);
    }

    protected function givenAFileId() : FileId
    {
        return FileId::next();
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenAFileWithId(FileId $fileId) : ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        return $file;
    }

    protected function givenAFilename() : string
    {
        return \uniqid();
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileHasFilename(ObjectProphecy $file, string $filename) : void
    {
        $file->filename()->willReturn($filename);
    }

    protected function givenFileContents() : string
    {
        return \uniqid();
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileHasContents(ObjectProphecy $file, string $contents) : void
    {
        $file->contents()->willReturn($contents);
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenFileManagerLoadsFileById(FileId $fileId) : ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileManager->load($fileId)->willReturn($file->reveal());
        return $file;
    }

    protected function givenFileManagerThrowsFileNotStoredExceptionWhileCreatingNewFile($filename = null, $contents = null, ?Message $trigger = null) : FileNotStoredException
    {
        $trigger = $trigger ?? Argument::any();
        $filename = $filename ?? Argument::any();
        $contents = $contents ?? Argument::any();
        $e = new FileNotStoredException();
        $this->fileManager->new($filename, $contents, $trigger)->willThrow($e);
        return $e;
    }

    protected function givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId = null, $contents = null, ?Message $trigger = null) : FileNotFoundException
    {
        $trigger = $trigger ?? Argument::any();
        $fileId = $fileId ?? Argument::any();
        $contents = $contents ?? Argument::any();
        $e = new FileNotFoundException();
        $this->fileManager->replaceContents($fileId, $contents, $trigger)->willThrow($e);
        return $e;
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileWasCreatedOn(ObjectProphecy $file, ?\DateTimeImmutable $createdOn) : void
    {
        $file->createdOn()->willReturn($createdOn);
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileWasUpdatedOn(ObjectProphecy $file, ?\DateTimeImmutable $updatedOn) : void
    {
        $file->updatedOn()->willReturn($updatedOn);
    }

    protected function givenFileManagerThrowsFileNotFoundExceptionWhileLoadingFileWithId(FileId $fileId) : void
    {
        $this->fileManager->load($fileId)->willThrow(new FileNotFoundException());
    }

    protected function givenFileManagerThrowsFileNotFoundInStorageExceptionWhileLoadingFileWithId(FileId $fileId) : void
    {
        $this->fileManager->load($fileId)->willThrow(new FileNotFoundInStorageException());
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenFileHasContentHash(ObjectProphecy $file, string $contentHash) : void
    {
        $file->contentHash()->willReturn($contentHash);
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenFileRepositoryFindsFileById(FileId $fileId) : ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileRepository->findOneById($fileId)->willReturn($file->reveal());
        return $file;
    }

    protected function givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById(FileId $fileId) : void
    {
        $this->fileRepository->findOneById($fileId)->willThrow(new FileNotFoundException());
    }

    protected function thenFileNotFoundExceptionShouldBeThrown() : void
    {
        $this->expectException(FileNotFoundException::class);
    }
}

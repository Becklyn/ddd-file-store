<?php

namespace C201\FileStore\Domain;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\Storage\FileNotStoredException;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-03
 *
 * @codeCoverageIgnore
 */
trait FileTestTrait
{
    /**
     * @var ObjectProphecy|FileManager
     */
    protected ObjectProphecy $fileManager;

    protected function initFilesTestTrait(): void
    {
        $this->fileManager = $this->prophesize(FileManager::class);
    }

    protected function givenAFileId(): FileId
    {
        return FileId::next();
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenAFileWithId(FileId $fileId): ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        return $file;
    }

    protected function givenAFilename(): string
    {
        return uniqid();
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileHasFilename(ObjectProphecy $file, string $filename): void
    {
        $file->filename()->willReturn($filename);
    }

    protected function givenFileContents(): string
    {
        return uniqid();
    }

    /**
     * @param ObjectProphecy|File $file
     */
    protected function givenFileHasContents(ObjectProphecy $file, string $contents): void
    {
        $file->contents()->willReturn($contents);
    }

    /**
     * @return ObjectProphecy|File
     */
    protected function givenFileManagerLoadsFileById(FileId $fileId): ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileManager->load($fileId)->willReturn($file->reveal());
        return $file;
    }

    protected function givenFileManagerThrowsFileNotStoredExceptionWhileCreatingNewFile($filename = null, $contents = null): FileNotStoredException
    {
        $filename = $filename ?? Argument::any();
        $contents = $contents ?? Argument::any();
        $e = new FileNotStoredException();
        $this->fileManager->new($filename, $contents)->willThrow($e);
        return $e;
    }

    protected function givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId = null, $contents = null): FileNotFoundException
    {
        $fileId = $fileId ?? Argument::any();
        $contents = $contents ?? Argument::any();
        $e = new FileNotFoundException();
        $this->fileManager->replaceContents($fileId, $contents)->willThrow($e);
        return $e;
    }
}

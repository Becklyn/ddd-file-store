<?php

namespace C201\FileStore\Domain;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-03
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
    protected function givenFileManagerLoadsFileById(FileId $fileId): ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileManager->load($fileId)->willReturn($file->reveal());
        return $file;
    }
}

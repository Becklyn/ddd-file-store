<?php

namespace Becklyn\FileStore\Application;

use Becklyn\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-07-28
 */
class DeleteFileCommand
{
    private FileId $fileId;

    public function __construct(FileId $fileId)
    {
        $this->fileId = $fileId;
    }

    public function fileId(): FileId
    {
        return $this->fileId;
    }
}

<?php

namespace C201\FileStore\Domain\Storage\Filesystem;

use C201\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
interface FilePointerRepository
{
    public function nextIdentity(): FilePointerId;

    public function add(FilePointer $filePointer): void;

    /**
     * @throws FilePointerNotFoundException
     */
    public function findOneByFileId(FileId $fileId): FilePointer;
}

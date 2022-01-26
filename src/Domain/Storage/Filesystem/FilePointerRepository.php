<?php

namespace Becklyn\FileStore\Domain\Storage\Filesystem;

use Becklyn\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
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

    public function remove(FilePointer $filePointer): void;
}

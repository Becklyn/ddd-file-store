<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\Storage\Filesystem;

use Becklyn\Ddd\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-27
 */
interface FilePointerRepository
{
    public function nextIdentity() : FilePointerId;

    public function add(FilePointer $filePointer) : void;

    /**
     * @throws FilePointerNotFoundException
     */
    public function findOneByFileId(FileId $fileId) : FilePointer;

    public function remove(FilePointer $filePointer) : void;
}

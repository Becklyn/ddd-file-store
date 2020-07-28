<?php

namespace C201\FileStore\Domain\File;

use C201\Ddd\Identity\Domain\AggregateId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 */
interface FileRepository
{
    public function nextIdentity(): FileId;

    public function add(File $file): void;

    /**
     * @throws FileNotFoundException
     */
    public function findOneById(FileId $fileId): File;

    public function remove(File $file): void;

    /**
     * @throws FileNotFoundException
     */
    public function findOneByOwnerId(AggregateId $ownerId): File;
}

<?php

namespace C201\FileStore\Domain\File;

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
}

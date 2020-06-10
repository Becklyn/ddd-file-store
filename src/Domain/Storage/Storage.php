<?php

namespace C201\FileStore\Domain\Storage;

use C201\FileStore\Domain\File\File;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
interface Storage
{
    /**
     * @throws FileNotStoredException
     */
    public function storeFileContents(File $file): void;

    /**
     * @throws FileNotFoundInStorageException
     */
    public function loadFileContents(File $file): string;
}

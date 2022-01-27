<?php declare(strict_types=1);

namespace Becklyn\FileStore\Domain\Storage;

use Becklyn\FileStore\Domain\File\File;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-27
 */
interface Storage
{
    /**
     * @throws FileNotStoredException
     */
    public function storeFileContents(File $file) : void;

    /**
     * @throws FileNotFoundInStorageException
     */
    public function loadFileContents(File $file) : string;

    public function deleteFileContents(File $file) : void;
}

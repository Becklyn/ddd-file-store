<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\Storage;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\Messages\Domain\Message;

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
    public function storeFileContents(File $file, Message $trigger) : void;

    /**
     * @throws FileNotFoundInStorageException
     */
    public function loadFileContents(File $file) : string;

    public function deleteFileContents(File $file, Message $trigger) : void;
}

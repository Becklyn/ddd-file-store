<?php declare(strict_types=1);

namespace Becklyn\FileStore\Application;

use Becklyn\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-27
 */
class RenameFileCommand
{
    private FileId $fileId;
    private string $filename;
    private ?string $errorMessage;

    public function __construct(FileId $fileId, string $filename, ?string $errorMessage = null)
    {
        $this->fileId = $fileId;
        $this->filename = $filename;
        $this->errorMessage = $errorMessage;
    }

    public function fileId() : FileId
    {
        return $this->fileId;
    }

    public function filename() : string
    {
        return $this->filename;
    }

    public function errorMessage() : ?string
    {
        return $this->errorMessage;
    }
}

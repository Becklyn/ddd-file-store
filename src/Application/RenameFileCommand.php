<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Domain\AbstractCommand;
use Becklyn\Ddd\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-27
 */
class RenameFileCommand extends AbstractCommand
{
    private FileId $fileId;
    private string $filename;
    private ?string $errorMessage;

    public function __construct(FileId $fileId, string $filename, ?string $errorMessage = null)
    {
        parent::__construct();

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

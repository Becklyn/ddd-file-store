<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Domain\AbstractCommand;
use Becklyn\Ddd\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-05
 */
class ReplaceFileContentsCommand extends AbstractCommand
{
    private FileId $fileId;
    private string $newContents;
    private ?string $errorMessage;

    public function __construct(FileId $fileId, string $newContents, ?string $errorMessage = null)
    {
        parent::__construct();

        $this->fileId = $fileId;
        $this->newContents = $newContents;
        $this->errorMessage = $errorMessage;
    }

    public function fileId() : FileId
    {
        return $this->fileId;
    }

    public function newContents() : string
    {
        return $this->newContents;
    }

    public function errorMessage() : ?string
    {
        return $this->errorMessage;
    }
}

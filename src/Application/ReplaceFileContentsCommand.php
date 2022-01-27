<?php declare(strict_types=1);

namespace Becklyn\FileStore\Application;

use Becklyn\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-05
 */
class ReplaceFileContentsCommand
{
    private FileId $id;
    private string $newContents;
    private ?string $errorMessage;

    public function __construct(FileId $id, string $newContents, ?string $errorMessage = null)
    {
        $this->id = $id;
        $this->newContents = $newContents;
        $this->errorMessage = $errorMessage;
    }

    public function id() : FileId
    {
        return $this->id;
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

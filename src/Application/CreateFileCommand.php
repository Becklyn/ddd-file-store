<?php

namespace Becklyn\FileStore\Application;

use Becklyn\Ddd\Identity\Domain\AggregateId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since  2020-06-04
 */
class CreateFileCommand
{
    private string $contents;
    private string $filename;
    private AggregateId $ownerId;
    private ?string $errorMessage;

    public function __construct(string $contents, string $filename, AggregateId $ownerId, string $errorMessage = null)
    {
        $this->contents = $contents;
        $this->filename = $filename;
        $this->ownerId = $ownerId;
        $this->errorMessage = $errorMessage;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function ownerId(): AggregateId
    {
        return $this->ownerId;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }
}

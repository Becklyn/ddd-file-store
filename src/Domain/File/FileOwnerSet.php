<?php

namespace Becklyn\FileStore\Domain\File;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 */
class FileOwnerSet extends AbstractDomainEvent
{
    private FileId $fileId;
    private string $ownerId;
    private string $ownerType;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FileId $fileId, string $ownerId, string $ownerType)
    {
        parent::__construct($id, $raisedTs);
        $this->fileId = $fileId;
        $this->ownerId = $ownerId;
        $this->ownerType = $ownerType;
    }

    public function aggregateId(): FileId
    {
        return $this->fileId;
    }

    public function aggregateType(): string
    {
        return File::class;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function ownerType(): string
    {
        return $this->ownerType;
    }
}

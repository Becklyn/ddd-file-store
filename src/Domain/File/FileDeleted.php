<?php

namespace C201\FileStore\Domain\File;

use C201\Ddd\Events\Domain\AbstractDomainEvent;
use C201\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 */
class FileDeleted extends AbstractDomainEvent
{
    private FileId $fileId;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FileId $fileId)
    {
        parent::__construct($id, $raisedTs);
        $this->fileId = $fileId;
    }

    public function aggregateId(): FileId
    {
        return $this->fileId;
    }

    public function aggregateType(): string
    {
        return File::class;
    }
}
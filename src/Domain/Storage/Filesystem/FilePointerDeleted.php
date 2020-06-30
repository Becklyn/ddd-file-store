<?php

namespace C201\FileStore\Domain\Storage\Filesystem;

use C201\Ddd\Events\Domain\AbstractDomainEvent;
use C201\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 */
class FilePointerDeleted extends AbstractDomainEvent
{
    private FilePointerId $filePointerId;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FilePointerId $filePointerId)
    {
        parent::__construct($id, $raisedTs);
        $this->filePointerId = $filePointerId;
    }

    public function aggregateId(): FilePointerId
    {
        return $this->filePointerId;
    }

    public function aggregateType(): string
    {
        return FilePointer::class;
    }
}

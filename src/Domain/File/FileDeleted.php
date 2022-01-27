<?php declare(strict_types=1);

namespace Becklyn\FileStore\Domain\File;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
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

    public function aggregateId() : FileId
    {
        return $this->fileId;
    }

    public function aggregateType() : string
    {
        return File::class;
    }
}

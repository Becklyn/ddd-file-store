<?php declare(strict_types=1);

namespace Becklyn\FileStore\Domain\File;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-26
 */
class FileRenamed extends AbstractDomainEvent
{
    private FileId $fileId;
    private string $filename;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FileId $fileId, string $filename)
    {
        parent::__construct($id, $raisedTs);
        $this->fileId = $fileId;
        $this->filename = $filename;
    }

    public function aggregateId() : FileId
    {
        return $this->fileId;
    }

    public function aggregateType() : string
    {
        return File::class;
    }

    public function filename() : string
    {
        return $this->filename;
    }
}

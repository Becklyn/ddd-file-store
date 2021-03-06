<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;
use Becklyn\Ddd\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-27
 */
class FilePointerCreated extends AbstractDomainEvent
{
    private FilePointerId $filePointerId;
    private FileId $fileId;
    private string $path;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FilePointerId $filePointerId, FileId $fileId, string $path)
    {
        parent::__construct($id, $raisedTs);
        $this->filePointerId = $filePointerId;
        $this->fileId = $fileId;
        $this->path = $path;
    }

    public function aggregateId() : FilePointerId
    {
        return $this->filePointerId;
    }

    public function aggregateType() : string
    {
        return FilePointer::class;
    }

    public function fileId() : FileId
    {
        return $this->fileId;
    }

    public function path() : string
    {
        return $this->path;
    }
}

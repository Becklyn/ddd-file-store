<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\File;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-26
 */
class FileContentsUpdated extends AbstractDomainEvent
{
    private FileId $fileId;
    private string $contentHash;
    private int $size;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, FileId $fileId, string $contentHash, int $size)
    {
        parent::__construct($id, $raisedTs);
        $this->fileId = $fileId;
        $this->contentHash = $contentHash;
        $this->size = $size;
    }

    public function aggregateId() : FileId
    {
        return $this->fileId;
    }

    public function aggregateType() : string
    {
        return File::class;
    }

    public function contentHash() : string
    {
        return $this->contentHash;
    }

    public function size() : int
    {
        return $this->size;
    }
}

<?php

namespace Becklyn\FileStore\Domain\Storage\Filesystem;

use Becklyn\FileStore\Domain\File\FileId;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\Events\Domain\EventProviderCapabilities;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since 2020-05-27
 *
 * @ORM\Entity
 * @ORM\Table(name="becklyn_filesystem_file_pointers")
 */
class FilePointer implements EventProvider
{
    use EventProviderCapabilities;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $internalId = null;

    /**
     * @ORM\Column(type="string", unique=true, length=36, nullable=false, name="uuid")
     */
    private string $id;

    /**
     * @ORM\Column(type="string", unique=true, length=36, nullable=false)
     */
    private string $fileId;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     */
    private string $path;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $createdTs;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $updatedTs;

    private function __construct(FilePointerId $id, FileId $fileId, string $path)
    {
        $this->id = $id->asString();
        $this->fileId = $fileId->asString();
        $this->path = $path;
        $this->createdTs = new \DateTimeImmutable();
        $this->updatedTs = $this->createdTs;
    }

    public static function create(FilePointerId $id, FileId $fileId, string $path): self
    {
        $filePointer = new static($id, $fileId, $path);
        $filePointer->raiseEvent(new FilePointerCreated($filePointer->nextEventIdentity(), new \DateTimeImmutable(), $id, $fileId, $path));
        return $filePointer;
    }

    public function id(): FilePointerId
    {
        return FilePointerId::fromString($this->id);
    }

    public function fileId(): FileId
    {
        return FileId::fromString($this->fileId);
    }

    public function path(): string
    {
        return $this->path;
    }
}

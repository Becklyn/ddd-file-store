<?php

namespace C201\FileStore\Domain\Storage\Filesystem;

use C201\FileStore\Domain\File\FileId;
use C201\Ddd\Events\Domain\EventProvider;
use C201\Ddd\Events\Domain\EventProviderCapabilities;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since 2020-05-27
 *
 * @ORM\Entity
 * @ORM\Table(name="c201_filesystem_file_pointers")
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
     * @Gedmo\Timestampable(on="create")
     */
    private ?\DateTimeImmutable $createdTs = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @Gedmo\Timestampable(on="update")
     */
    private ?\DateTimeImmutable $updatedTs = null;

    private function __construct(FilePointerId $id, FileId $fileId, string $path)
    {
        $this->id = $id->asString();
        $this->fileId = $fileId->asString();
        $this->path = $path;
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

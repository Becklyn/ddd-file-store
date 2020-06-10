<?php

namespace C201\FileStore\Domain\File;

use C201\Ddd\Events\Domain\EventProvider;
use C201\Ddd\Events\Domain\EventProviderCapabilities;
use C201\Ddd\Identity\Domain\AggregateId;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-26
 *
 * @ORM\Entity
 * @ORM\Table(name="c201_files")
 */
class File implements EventProvider
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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $filename;

    private ?string $contents = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $contentHash;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private int $size;

    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private ?string $ownerId = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $ownerType = null;

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

    public function __construct(FileId $id, string $filename, string $contents)
    {
        $this->id = $id->asString();
        $this->filename = $filename;
        $this->setContents($contents);
    }

    private function setContents(string $contents): void
    {
        $this->contentHash = $this->hashContents($contents);
        $this->size = strlen($contents);
        $this->contents = $contents;
    }

    private function hashContents(string $contents): string
    {
        return sha1($contents);
    }

    public static function create(FileId $id, string $filename, string $contents): self
    {
        $file = new static($id, $filename, $contents);
        $file->raiseEvent(new FileCreated($file->nextEventIdentity(), new \DateTimeImmutable(), $id, $filename, $file->contentHash, $file->size));
        return $file;
    }

    public function id(): FileId
    {
        return FileId::fromString($this->id);
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function contents(): string
    {
        if ($this->contents === null) {
            throw new \LogicException("The contents of file '$this->id' have not been loaded yet");
        }

        return $this->contents;
    }

    public function contentHash(): string
    {
        return $this->contentHash;
    }

    /**
     * @return int File size in bytes
     */
    public function size(): int
    {
        return $this->size;
    }

    public function ownerId(): AggregateId
    {
        $idClass = "{$this->ownerType}Id";
        return $idClass::fromString($this->ownerId);
    }

    public function ownerType(): string
    {
        return $this->ownerType;
    }

    public function load(string $contents): self
    {
        if ($this->hashContents($contents) !== $this->contentHash) {
            $newHash = $this->hashContents($contents);
            throw new \LogicException("Attempted to load file '$this->id' with contents that do not match the file\'s content hash '$this->contentHash'. Hash of supplied contents: '$newHash'.");
        }

        $this->contents = $contents;
        return $this;
    }

    public function rename(string $newFilename): self
    {
        $this->filename = $newFilename;
        $this->raiseEvent(new FileRenamed($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $newFilename));

        return $this;
    }

    public function updateContents(string $newContents): self
    {
        $this->setContents($newContents);
        $this->raiseEvent(new FileContentsUpdated($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $this->contentHash, $this->size));

        return $this;
    }

    public function setOwner(AggregateId $ownerId): self
    {
        $this->ownerId = $ownerId->asString();
        // As aggregate id classes will be called \Some\Namespace\ClassId, stripping the last two characters will give the name of the class the id belongs to
        $this->ownerType = substr(get_class($ownerId), 0, -2);
        $this->raiseEvent(new FileOwnerSet($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $this->ownerId, $this->ownerType));

        return $this;
    }
}
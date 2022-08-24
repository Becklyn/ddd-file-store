<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\File;

use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\Events\Domain\EventProviderCapabilities;
use Becklyn\Ddd\Identity\Domain\AggregateId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-26
 *
 * @ORM\Entity
 * @ORM\Table(name="becklyn_files")
 */
class File implements EventProvider
{
    use EventProviderCapabilities;

    /**
     * @ORM\Id
     * @ORM\Column(name="uuid", type="string", length=36)
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @ORM\Column(name="file_size", type="integer", nullable=false, options={"unsigned"=true})
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
     */
    private \DateTimeImmutable $createdTs;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $updatedTs;

    private function __construct(FileId $id, string $filename, string $contents)
    {
        $this->id = $id->asString();
        $this->filename = $filename;
        $this->setContents($contents);
        $this->createdTs = new \DateTimeImmutable();
        $this->updatedTs = $this->createdTs;
    }

    private function setContents(string $contents) : void
    {
        $this->contentHash = $this->hashContents($contents);
        $this->size = \strlen($contents);
        $this->contents = $contents;
        $this->updatedTs = new \DateTimeImmutable();
    }

    private function hashContents(string $contents) : string
    {
        return \sha1($contents);
    }

    public static function create(FileId $id, string $filename, string $contents) : self
    {
        $file = new self($id, $filename, $contents);
        $file->raiseEvent(new FileCreated($file->nextEventIdentity(), new \DateTimeImmutable(), $id, $filename, $file->contentHash, $file->size));
        return $file;
    }

    public function id() : FileId
    {
        return FileId::fromString($this->id);
    }

    public function filename() : string
    {
        return $this->filename;
    }

    public function contents() : string
    {
        if (null === $this->contents) {
            throw new \LogicException("The contents of file '{$this->id}' have not been loaded yet");
        }

        return $this->contents;
    }

    public function contentHash() : string
    {
        return $this->contentHash;
    }

    /**
     * @return int File size in bytes
     */
    public function size() : int
    {
        return $this->size;
    }

    public function ownerId() : AggregateId
    {
        $idClass = "{$this->ownerType}Id";
        return $idClass::fromString($this->ownerId);
    }

    public function ownerType() : string
    {
        return $this->ownerType;
    }

    public function type() : string
    {
        $filenameExplosion = \explode('.', $this->filename);
        return \end($filenameExplosion);
    }

    public function createdOn() : ?\DateTimeImmutable
    {
        return $this->createdTs;
    }

    public function updatedOn() : ?\DateTimeImmutable
    {
        return $this->updatedTs;
    }

    public function load(string $contents) : self
    {
        if ($this->hashContents($contents) !== $this->contentHash) {
            $newHash = $this->hashContents($contents);
            throw new \LogicException("Attempted to load file '{$this->id}' with contents that do not match the file\\'s content hash '{$this->contentHash}'. Hash of supplied contents: '{$newHash}'.");
        }

        $this->contents = $contents;
        return $this;
    }

    public function rename(string $newFilename) : self
    {
        if ($this->filename === $newFilename) {
            return $this;
        }

        $this->filename = $newFilename;
        $this->updatedTs = new \DateTimeImmutable();
        $this->raiseEvent(new FileRenamed($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $newFilename));

        return $this;
    }

    public function updateContents(string $newContents) : self
    {
        if ($this->contentHash === $this->hashContents($newContents)) {
            return $this;
        }

        $this->setContents($newContents);
        $this->raiseEvent(new FileContentsUpdated($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $this->contentHash, $this->size));

        return $this;
    }

    public function setOwner(AggregateId $ownerId) : self
    {
        $this->ownerId = $ownerId->asString();
        $this->ownerType = $ownerId->aggregateType();
        $this->updatedTs = new \DateTimeImmutable();
        $this->raiseEvent(new FileOwnerSet($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $this->ownerId, $this->ownerType));

        return $this;
    }

    // Should only be called by a FileRepository
    public function delete() : void
    {
        $this->raiseEvent(new FileDeleted($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id()));
    }
}

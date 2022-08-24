<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain\File;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileContentsUpdated;
use Becklyn\Ddd\FileStore\Domain\File\FileCreated;
use Becklyn\Ddd\FileStore\Domain\File\FileDeleted;
use Becklyn\Ddd\FileStore\Domain\File\FileOwnerSet;
use Becklyn\Ddd\FileStore\Domain\File\FileRenamed;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Domain\File\File
 */
class FileTest extends TestCase
{
    use ProphecyTrait;
    use FileTestTrait;

    public function testCreateReturnsFileWithPassedIdFilenameAndContents() : void
    {
        $id = $this->givenAFileId();
        $filename = \uniqid();
        $contents = \uniqid();

        $file = File::create($id, $filename, $contents);
        self::assertTrue($id->equals($file->id()));
        self::assertEquals($filename, $file->filename());
        self::assertEquals($contents, $file->contents());
        self::assertNotNull($file->createdOn());
        self::assertSame($file->createdOn(), $file->updatedOn());
    }

    public function testCreateRaisesFileCreatedEvent() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $events = $file->dequeueEvents();
        self::assertNotEmpty($events);
        self::assertContainsOnly(FileCreated::class, $events);
        /** @var FileCreated $event */
        $event = $events[0];
        self::assertTrue($file->id()->equals($event->aggregateId()));
        self::assertEquals($file->filename(), $event->filename());
        self::assertEquals($file->size(), $event->size());
        self::assertEquals($file->contentHash(), $event->contentHash());
    }

    public function testContentHashReturnsSha1OfContentsPassedToCreate() : void
    {
        $contents = \uniqid();

        $file = File::create($this->givenAFileId(), \uniqid(), $contents);
        self::assertEquals(\sha1($contents), $file->contentHash());
    }

    public function testSizeReturnsContentLengthInBytes() : void
    {
        $contents = 'fööbär';

        $file = File::create($this->givenAFileId(), \uniqid(), $contents);
        self::assertEquals(\strlen($contents), $file->size());
    }

    public function testTypeReturnsFileExtensionDerivedFromFilename() : void
    {
        $extension = \uniqid();
        $filename = \uniqid() . '.' . $extension;

        $file = File::create($this->givenAFileId(), $filename, \uniqid());
        self::assertEquals($extension, $file->type());
    }

    public function testOwnerIdReturnsAggregateIdSetBySetOwner() : void
    {
        $ownerId = TestProxyAggregateId::next();

        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $file->setOwner($ownerId);

        self::assertTrue($ownerId->equals($file->ownerId()));
    }

    public function testOwnerTypeReturnsFqnOfClassForWhichAggregateIdWasPassedToSetOwner() : void
    {
        $ownerId = TestProxyAggregateId::next();
        // FQN of ofwner is equal to FQN of id class without the "Id" at the end
        $ownerFqn = \substr(\get_class($ownerId), 0, -2);

        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $file->setOwner($ownerId);

        self::assertEquals($ownerFqn, $file->ownerType());
    }

    public function testSetOwnerRaisesFileOwnerSetEvent() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $file->dequeueEvents();

        $file->setOwner(TestProxyAggregateId::next());

        $events = $file->dequeueEvents();
        self::assertNotEmpty($events);
        self::assertContainsOnly(FileOwnerSet::class, $events);
        /** @var FileOwnerSet $event */
        $event = $events[0];
        self::assertTrue($file->id()->equals($event->aggregateId()));
        self::assertEquals($file->ownerId()->asString(), $event->ownerId());
        self::assertEquals($file->ownerType(), $event->ownerType());
    }

    public function testLoadThrowsLogicExceptionIfPassedContentsAreDifferentFromContentsFileWasCreatedWith() : void
    {
        $originalContents = \uniqid();
        $differentContents = \uniqid();

        $file = File::create($this->givenAFileId(), \uniqid(), $originalContents);

        $this->expectException(\LogicException::class);
        $file->load($differentContents);
    }

    public function testContentsReturnsContentsPassedToLoad() : void
    {
        $contents = \uniqid();
        $file = File::create($this->givenAFileId(), \uniqid(), $contents);

        $file->load($contents);
        self::assertEquals($contents, $file->contents());
    }

    public function testRenameSetsNewFilename() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());

        $newFilename = \uniqid();
        self::assertNotEquals($newFilename, $file->filename());

        $file->rename($newFilename);
        self::assertEquals($newFilename, $file->filename());
    }

    public function testRenameRaisesFileRenamedEvent() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $file->dequeueEvents();

        $file->rename(\uniqid());

        $events = $file->dequeueEvents();
        self::assertNotEmpty($events);
        self::assertContainsOnly(FileRenamed::class, $events);
        /** @var FileRenamed $event */
        $event = $events[0];
        self::assertTrue($file->id()->equals($event->aggregateId()));
        self::assertEquals($file->filename(), $event->filename());
    }

    public function testRenameRaisesNoEventsWhenNewNameIsSameAsOldName() : void
    {
        $filename = $this->givenAFilename();
        $file = File::create($this->givenAFileId(), $filename, \uniqid());
        $file->dequeueEvents();

        $file->rename($filename);
        self::assertEmpty($file->dequeueEvents());
    }

    public function testUpdateContentsSetsNewContentsTheirSizeAndHash() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());

        $newContents = \uniqid();
        self::assertNotEquals($newContents, $file->contents());

        $file->updateContents($newContents);
        self::assertEquals($newContents, $file->contents());
        self::assertEquals(\strlen($newContents), $file->size());
        self::assertEquals(\sha1($newContents), $file->contentHash());
    }

    public function testUpdateContentsRaisesFileContentsUpdatedEvent() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), \uniqid());
        $file->dequeueEvents();

        $newContents = \uniqid();
        $file->updateContents($newContents);

        $events = $file->dequeueEvents();
        self::assertNotEmpty($events);
        self::assertContainsOnly(FileContentsUpdated::class, $events);
        /** @var FileContentsUpdated $event */
        $event = $events[0];
        self::assertTrue($file->id()->equals($event->aggregateId()));
        self::assertEquals($file->size(), $event->size());
        self::assertEquals($file->contentHash(), $event->contentHash());
    }

    public function testUpdateContentsRaisesNoEventsWhenNewContentsAreSameAsOldOnes() : void
    {
        $contents = $this->givenFileContents();
        $file = File::create($this->givenAFileId(), \uniqid(), $contents);
        $file->dequeueEvents();

        $file->updateContents($contents);
        self::assertEmpty($file->dequeueEvents());
    }

    public function testDeleteRaisesFileDeletedEvent() : void
    {
        $file = File::create($this->givenAFileId(), \uniqid(), $this->givenFileContents());
        $file->dequeueEvents();
        self::assertEmpty($file->dequeueEvents());

        $file->delete();
        $events = $file->dequeueEvents();
        self::assertCount(1, $events);
        self::assertContainsOnlyInstancesOf(FileDeleted::class, $events);
        self::assertTrue($file->id()->equals($events[0]->aggregateId()));
    }
}

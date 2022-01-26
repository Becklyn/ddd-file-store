<?php

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileContentsUpdated;
use Becklyn\FileStore\Domain\File\FileCreated;
use Becklyn\FileStore\Domain\File\FileOwnerSet;
use Becklyn\FileStore\Domain\File\FileRenamed;
use Becklyn\FileStore\Testing\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\File
 */
class FileTest extends TestCase
{
    use ProphecyTrait;
    use FileTestTrait;

    public function testCreateReturnsFileWithPassedIdFilenameAndContents(): void
    {
        $id = $this->givenAFileId();
        $filename = uniqid();
        $contents = uniqid();

        $file = File::create($id, $filename, $contents);
        $this->assertTrue($id->equals($file->id()));
        $this->assertEquals($filename, $file->filename());
        $this->assertEquals($contents, $file->contents());
        $this->assertNull($file->createdOn());
        $this->assertNull($file->updatedOn());
    }

    public function testCreateRaisesFileCreatedEvent(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $events = $file->dequeueEvents();
        $this->assertNotEmpty($events);
        $this->assertContainsOnly(FileCreated::class, $events);
        /** @var FileCreated $event */
        $event = $events[0];
        $this->assertTrue($file->id()->equals($event->aggregateId()));
        $this->assertEquals($file->filename(), $event->filename());
        $this->assertEquals($file->size(), $event->size());
        $this->assertEquals($file->contentHash(), $event->contentHash());
    }

    public function testContentHashReturnsSha1OfContentsPassedToCreate(): void
    {
        $contents = uniqid();

        $file = File::create($this->givenAFileId(), uniqid(), $contents);
        $this->assertEquals(sha1($contents), $file->contentHash());
    }

    public function testSizeReturnsContentLengthInBytes(): void
    {
        $contents = 'fööbär';

        $file = File::create($this->givenAFileId(), uniqid(), $contents);
        $this->assertEquals(strlen($contents), $file->size());
    }

    public function testTypeReturnsFileExtensionDerivedFromFilename(): void
    {
        $extension = uniqid();
        $filename = uniqid() . '.' . $extension;

        $file = File::create($this->givenAFileId(), $filename, uniqid());
        $this->assertEquals($extension, $file->type());
    }

    public function testOwnerIdReturnsAggregateIdSetBySetOwner(): void
    {
        $ownerId = TestProxyAggregateId::next();

        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $file->setOwner($ownerId);

        $this->assertTrue($ownerId->equals($file->ownerId()));
    }

    public function testOwnerTypeReturnsFqnOfClassForWhichAggregateIdWasPassedToSetOwner(): void
    {
        $ownerId = TestProxyAggregateId::next();
        // FQN of ofwner is equal to FQN of id class without the "Id" at the end
        $ownerFqn = substr(get_class($ownerId), 0, -2);

        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $file->setOwner($ownerId);

        $this->assertEquals($ownerFqn, $file->ownerType());
    }

    public function testSetOwnerRaisesFileOwnerSetEvent(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $file->dequeueEvents();

        $file->setOwner(TestProxyAggregateId::next());

        $events = $file->dequeueEvents();
        $this->assertNotEmpty($events);
        $this->assertContainsOnly(FileOwnerSet::class, $events);
        /** @var FileOwnerSet $event */
        $event = $events[0];
        $this->assertTrue($file->id()->equals($event->aggregateId()));
        $this->assertEquals($file->ownerId()->asString(), $event->ownerId());
        $this->assertEquals($file->ownerType(), $event->ownerType());
    }

    public function testLoadThrowsLogicExceptionIfPassedContentsAreDifferentFromContentsFileWasCreatedWith(): void
    {
        $originalContents = uniqid();
        $differentContents = uniqid();

        $file = File::create($this->givenAFileId(), uniqid(), $originalContents);

        $this->expectException(\LogicException::class);
        $file->load($differentContents);
    }

    public function testContentsReturnsContentsPassedToLoad(): void
    {
        $contents = uniqid();
        $file = File::create($this->givenAFileId(), uniqid(), $contents);

        $file->load($contents);
        $this->assertEquals($contents, $file->contents());
    }

    public function testRenameSetsNewFilename(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());

        $newFilename = uniqid();
        $this->assertNotEquals($newFilename, $file->filename());

        $file->rename($newFilename);
        $this->assertEquals($newFilename, $file->filename());
    }

    public function testRenameRaisesFileRenamedEvent(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $file->dequeueEvents();

        $file->rename(uniqid());

        $events = $file->dequeueEvents();
        $this->assertNotEmpty($events);
        $this->assertContainsOnly(FileRenamed::class, $events);
        /** @var FileRenamed $event */
        $event = $events[0];
        $this->assertTrue($file->id()->equals($event->aggregateId()));
        $this->assertEquals($file->filename(), $event->filename());
    }

    public function testRenameRaisesNoEventsWhenNewNameIsSameAsOldName(): void
    {
        $filename = $this->givenAFilename();
        $file = File::create($this->givenAFileId(), $filename, uniqid());
        $file->dequeueEvents();

        $file->rename($filename);
        $this->assertEmpty($file->dequeueEvents());
    }

    public function testUpdateContentsSetsNewContentsTheirSizeAndHash(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());

        $newContents = uniqid();
        $this->assertNotEquals($newContents, $file->contents());

        $file->updateContents($newContents);
        $this->assertEquals($newContents, $file->contents());
        $this->assertEquals(strlen($newContents), $file->size());
        $this->assertEquals(sha1($newContents), $file->contentHash());
    }

    public function testUpdateContentsRaisesFileContentsUpdatedEvent(): void
    {
        $file = File::create($this->givenAFileId(), uniqid(), uniqid());
        $file->dequeueEvents();

        $newContents = uniqid();
        $file->updateContents($newContents);

        $events = $file->dequeueEvents();
        $this->assertNotEmpty($events);
        $this->assertContainsOnly(FileContentsUpdated::class, $events);
        /** @var FileContentsUpdated $event */
        $event = $events[0];
        $this->assertTrue($file->id()->equals($event->aggregateId()));
        $this->assertEquals($file->size(), $event->size());
        $this->assertEquals($file->contentHash(), $event->contentHash());
    }

    public function testUpdateContentsRaisesNoEventsWhenNewContentsAreSameAsOldOnes(): void
    {
        $contents = $this->givenFileContents();
        $file = File::create($this->givenAFileId(), uniqid(), $contents);
        $file->dequeueEvents();

        $file->updateContents($contents);
        $this->assertEmpty($file->dequeueEvents());
    }
}

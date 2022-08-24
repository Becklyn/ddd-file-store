<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerCreated;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerDeleted;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerId;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointer
 */
class FilePointerTest extends TestCase
{
    use ProphecyTrait;
    use FileTestTrait;

    public function testCreateReturnsFilePointerWithIdFileIdAndPathPassedToConstructor() : void
    {
        $pointerId = $this->givenAFilePointerId();
        $fileId = $this->givenAFileId();
        $path = \uniqid();

        $pointer = FilePointer::create($pointerId, $fileId, $path);
        self::assertTrue($pointerId->equals($pointer->id()));
        self::assertTrue($fileId->equals($pointer->fileId()));
        self::assertEquals($path, $pointer->path());
    }

    private function givenAFilePointerId() : FilePointerId
    {
        return FilePointerId::next();
    }

    public function testCreateRaisesFilePointerCreatedEvent() : void
    {
        $pointerId = $this->givenAFilePointerId();
        $fileId = $this->givenAFileId();
        $path = \uniqid();

        $pointer = FilePointer::create($pointerId, $fileId, $path);
        $events = $pointer->dequeueEvents();
        self::assertNotEmpty($events);
        self::assertContainsOnly(FilePointerCreated::class, $events);
        /** @var FilePointerCreated $event */
        $event = $events[0];
        self::assertTrue($pointerId->equals($event->aggregateId()));
        self::assertTrue($fileId->equals($event->fileId()));
        self::assertEquals($path, $event->path());
    }

    public function testDeleteRaisesFilePointerDeletedEvent() : void
    {
        $filePointer = FilePointer::create($this->givenAFilePointerId(), $this->givenAFileId(), \uniqid());
        $filePointer->dequeueEvents();
        self::assertEmpty($filePointer->dequeueEvents());

        $filePointer->delete();
        $events = $filePointer->dequeueEvents();
        self::assertCount(1, $events);
        self::assertContainsOnlyInstancesOf(FilePointerDeleted::class, $events);
        self::assertTrue($filePointer->id()->equals($events[0]->aggregateId()));
    }
}

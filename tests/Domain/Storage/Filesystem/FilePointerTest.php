<?php

namespace Becklyn\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\FileStore\Domain\FileTestTrait;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerCreated;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerId;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\Storage\Filesystem\FilePointer
 */
class FilePointerTest extends TestCase
{
    use ProphecyTrait;
    use FileTestTrait;

    public function testCreateReturnsFilePointerWithIdFileIdAndPathPassedToConstructor(): void
    {
        $pointerId = $this->givenAFilePointerId();
        $fileId = $this->givenAFileId();
        $path = uniqid();

        $pointer = FilePointer::create($pointerId, $fileId, $path);
        $this->assertTrue($pointerId->equals($pointer->id()));
        $this->assertTrue($fileId->equals($pointer->fileId()));
        $this->assertEquals($path, $pointer->path());
    }

    private function givenAFilePointerId(): FilePointerId
    {
        return FilePointerId::next();
    }

    public function testCreateRaisesFilePointerCreatedEvent(): void
    {
        $pointerId = $this->givenAFilePointerId();
        $fileId = $this->givenAFileId();
        $path = uniqid();

        $pointer = FilePointer::create($pointerId, $fileId, $path);
        $events = $pointer->dequeueEvents();
        $this->assertNotEmpty($events);
        $this->assertContainsOnly(FilePointerCreated::class, $events);
        /** @var FilePointerCreated $event */
        $event = $events[0];
        $this->assertTrue($pointerId->equals($event->aggregateId()));
        $this->assertTrue($fileId->equals($event->fileId()));
        $this->assertEquals($path, $event->path());
    }
}

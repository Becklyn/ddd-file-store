<?php

namespace C201\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\Storage\Filesystem\FilePointer;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerCreated;
use C201\FileStore\Domain\Storage\Filesystem\FilePointerId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\Storage\Filesystem\FilePointerCreated
 */
class FilePointerCreatedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $filePointerId = FilePointerId::next();
        $fileId = FileId::next();
        $path = uniqid();

        $event = new FilePointerCreated($this->givenAnEventId(), $this->givenARaisedTs(), $filePointerId, $fileId, $path);
        $this->assertTrue($filePointerId->equals($event->aggregateId()));
        $this->assertTrue($fileId->equals($event->fileId()));
        $this->assertEquals($path, $event->path());
        $this->assertEquals(FilePointer::class, $event->aggregateType());
    }
}

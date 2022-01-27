<?php declare(strict_types=1);

namespace Becklyn\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\File\FileId;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerCreated;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerCreated
 */
class FilePointerCreatedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $filePointerId = FilePointerId::next();
        $fileId = FileId::next();
        $path = \uniqid();

        $event = new FilePointerCreated($this->givenAnEventId(), $this->givenARaisedTs(), $filePointerId, $fileId, $path);
        self::assertTrue($filePointerId->equals($event->aggregateId()));
        self::assertTrue($fileId->equals($event->fileId()));
        self::assertEquals($path, $event->path());
        self::assertEquals(FilePointer::class, $event->aggregateType());
    }
}

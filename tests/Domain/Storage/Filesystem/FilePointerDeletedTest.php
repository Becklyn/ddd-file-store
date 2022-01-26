<?php

namespace Becklyn\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerDeleted;
use Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\Storage\Filesystem\FilePointerDeleted
 */
class FilePointerDeletedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $filePointerId = FilePointerId::next();

        $event = new FilePointerDeleted($this->givenAnEventId(), $this->givenARaisedTs(), $filePointerId);
        $this->assertTrue($filePointerId->equals($event->aggregateId()));
        $this->assertEquals(FilePointer::class, $event->aggregateType());
    }
}

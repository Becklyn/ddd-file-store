<?php

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileDeleted;
use Becklyn\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\FileDeleted
 */
class FileDeletedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $fileId = FileId::next();

        $event = new FileDeleted($this->givenAnEventId(), $this->givenARaisedTs(), $fileId);
        $this->assertEquals($fileId, $event->aggregateId());
        $this->assertEquals(File::class, $event->aggregateType());
    }
}

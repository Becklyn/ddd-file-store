<?php

namespace C201\FileStore\Tests\Domain\File;

use C201\Ddd\Events\Domain\DomainEventTestTrait;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileDeleted;
use C201\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\File\FileDeleted
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

<?php

namespace C201\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileOwnerSet;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\File\FileOwnerSet
 */
class FileOwnerSetTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $fileId = FileId::next();
        $ownerId = uniqid();
        $ownerType = uniqid();

        $event = new FileOwnerSet($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $ownerId, $ownerType);
        $this->assertEquals($fileId, $event->aggregateId());
        $this->assertEquals($ownerId, $event->ownerId());
        $this->assertEquals($ownerType, $event->ownerType());
        $this->assertEquals(File::class, $event->aggregateType());
    }
}

<?php

namespace C201\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileCreated;
use C201\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\File\FileCreated
 */
class FileCreatedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $fileId = FileId::next();
        $filename = uniqid();
        $contentHash = uniqid();
        $size = random_int(1, 10900);

        $event = new FileCreated($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $filename, $contentHash, $size);
        $this->assertEquals($fileId, $event->aggregateId());
        $this->assertEquals($filename, $event->filename());
        $this->assertEquals($contentHash, $event->contentHash());
        $this->assertEquals($size, $event->size());
        $this->assertEquals(File::class, $event->aggregateType());
    }
}

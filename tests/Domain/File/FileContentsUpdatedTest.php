<?php

namespace C201\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileContentsUpdated;
use C201\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\File\FileContentsUpdated
 */
class FileContentsUpdatedTest extends TestCase
{
    use EventCreatorCapabilities;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $id = $this->nextEventIdentity();
        $raisedTs = new \DateTimeImmutable();
        $fileId = FileId::next();
        $contentHash = uniqid();
        $size = random_int(1, 10900);

        $event = new FileContentsUpdated($id, $raisedTs, $fileId, $contentHash, $size);
        $this->assertEquals($id, $event->id());
        $this->assertEquals($raisedTs, $event->raisedTs());
        $this->assertEquals($fileId, $event->aggregateId());
        $this->assertEquals($contentHash, $event->contentHash());
        $this->assertEquals($size, $event->size());
        $this->assertEquals(File::class, $event->aggregateType());
    }
}

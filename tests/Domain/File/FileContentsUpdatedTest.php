<?php declare(strict_types=1);

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Domain\EventCreatorCapabilities;
use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileContentsUpdated;
use Becklyn\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\FileContentsUpdated
 */
class FileContentsUpdatedTest extends TestCase
{
    use EventCreatorCapabilities;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $id = $this->nextEventIdentity();
        $raisedTs = new \DateTimeImmutable();
        $fileId = FileId::next();
        $contentHash = \uniqid();
        $size = \random_int(1, 10900);

        $event = new FileContentsUpdated($id, $raisedTs, $fileId, $contentHash, $size);
        self::assertEquals($id, $event->id());
        self::assertEquals($raisedTs, $event->raisedTs());
        self::assertEquals($fileId, $event->aggregateId());
        self::assertEquals($contentHash, $event->contentHash());
        self::assertEquals($size, $event->size());
        self::assertEquals(File::class, $event->aggregateType());
    }
}

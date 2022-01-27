<?php declare(strict_types=1);

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileCreated;
use Becklyn\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\FileCreated
 */
class FileCreatedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $fileId = FileId::next();
        $filename = \uniqid();
        $contentHash = \uniqid();
        $size = \random_int(1, 10900);

        $event = new FileCreated($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $filename, $contentHash, $size);
        self::assertEquals($fileId, $event->aggregateId());
        self::assertEquals($filename, $event->filename());
        self::assertEquals($contentHash, $event->contentHash());
        self::assertEquals($size, $event->size());
        self::assertEquals(File::class, $event->aggregateType());
    }
}

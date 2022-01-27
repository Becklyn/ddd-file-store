<?php declare(strict_types=1);

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileDeleted;
use Becklyn\FileStore\Domain\File\FileId;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\FileDeleted
 */
class FileDeletedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $fileId = FileId::next();

        $event = new FileDeleted($this->givenAnEventId(), $this->givenARaisedTs(), $fileId);
        self::assertEquals($fileId, $event->aggregateId());
        self::assertEquals(File::class, $event->aggregateType());
    }
}

<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileOwnerSet;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Domain\File\FileOwnerSet
 */
class FileOwnerSetTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $fileId = FileId::next();
        $ownerId = \uniqid();
        $ownerType = \uniqid();

        $event = new FileOwnerSet($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $ownerId, $ownerType);
        self::assertEquals($fileId, $event->aggregateId());
        self::assertEquals($ownerId, $event->ownerId());
        self::assertEquals($ownerType, $event->ownerType());
        self::assertEquals(File::class, $event->aggregateType());
    }
}

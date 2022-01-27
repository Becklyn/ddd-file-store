<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\File\FileRenamed;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Domain\File\FileRenamed
 */
class FileRenamedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor() : void
    {
        $fileId = FileId::next();
        $filename = \uniqid();

        $event = new FileRenamed($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $filename);
        self::assertEquals($fileId, $event->aggregateId());
        self::assertEquals($filename, $event->filename());
        self::assertEquals(File::class, $event->aggregateType());
    }
}

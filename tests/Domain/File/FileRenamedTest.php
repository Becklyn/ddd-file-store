<?php

namespace C201\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileRenamed;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\File\FileRenamed
 */
class FileRenamedTest extends TestCase
{
    use DomainEventTestTrait;

    public function testGettersReturnValuesPassedToConstructor(): void
    {
        $fileId = FileId::next();
        $filename = uniqid();

        $event = new FileRenamed($this->givenAnEventId(), $this->givenARaisedTs(), $fileId, $filename);
        $this->assertEquals($fileId, $event->aggregateId());
        $this->assertEquals($filename, $event->filename());
        $this->assertEquals(File::class, $event->aggregateType());
    }
}

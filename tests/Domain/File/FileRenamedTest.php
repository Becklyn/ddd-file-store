<?php

namespace Becklyn\FileStore\Tests\Domain\File;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\FileStore\Domain\File\File;
use Becklyn\FileStore\Domain\File\FileId;
use Becklyn\FileStore\Domain\File\FileRenamed;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\File\FileRenamed
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

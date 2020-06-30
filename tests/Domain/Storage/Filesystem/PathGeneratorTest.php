<?php

namespace C201\FileStore\Tests\Domain\Storage\Filesystem;

use C201\FileStore\Domain\Storage\Filesystem\PathGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\Storage\Filesystem\PathGenerator
 */
class PathGeneratorTest extends TestCase
{
    public function testGenerateReturnsPathForFilenameWithoutExtension(): void
    {
        $fixture = new PathGenerator();
        $this->assertNotEmpty($fixture->generate(uniqid()));
    }

    public function testGenerateReturnsPathForFilenameWithExtension(): void
    {
        $fixture = new PathGenerator();
        $this->assertNotEmpty($fixture->generate(uniqid() . '.pdf'));
    }

    public function testGenerateReturnsPathForEmptyFilename(): void
    {
        $fixture = new PathGenerator();
        $this->assertNotEmpty($fixture->generate());
    }
}

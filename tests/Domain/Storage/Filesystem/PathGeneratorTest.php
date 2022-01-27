<?php declare(strict_types=1);

namespace Becklyn\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\FileStore\Domain\Storage\Filesystem\PathGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\FileStore\Domain\Storage\Filesystem\PathGenerator
 */
class PathGeneratorTest extends TestCase
{
    public function testGenerateReturnsPathForFilenameWithoutExtension() : void
    {
        $fixture = new PathGenerator();
        self::assertNotEmpty($fixture->generate(\uniqid()));
    }

    public function testGenerateReturnsPathForFilenameWithExtension() : void
    {
        $fixture = new PathGenerator();
        self::assertNotEmpty($fixture->generate(\uniqid() . '.pdf'));
    }

    public function testGenerateReturnsPathForEmptyFilename() : void
    {
        $fixture = new PathGenerator();
        self::assertNotEmpty($fixture->generate());
    }
}

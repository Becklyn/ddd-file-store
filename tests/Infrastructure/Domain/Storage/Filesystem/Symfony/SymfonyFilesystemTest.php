<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Infrastructure\Domain\Storage\Filesystem\Symfony;

use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FileNotFoundInFilesystemException;
use Becklyn\Ddd\FileStore\Infrastructure\Domain\Storage\Filesystem\Symfony\SymfonyFilesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-30
 *
 * @covers \Becklyn\Ddd\FileStore\Infrastructure\Domain\Storage\Filesystem\Symfony\SymfonyFilesystem
 */
class SymfonyFilesystemTest extends TestCase
{
    use ProphecyTrait;

    protected string $baseFilesystemPath;

    /**
     * @var ObjectProphecy|Filesystem
     */
    protected ObjectProphecy $filesystem;

    protected SymfonyFilesystem $fixture;

    protected function setUp() : void
    {
        $this->baseFilesystemPath = \uniqid();
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->fixture = new SymfonyFilesystem($this->baseFilesystemPath, $this->filesystem->reveal());
    }

    public function testDumpFileDumpsContentsToFullPathConstructedFromRelativePath() : void
    {
        $relativePath = \uniqid();
        $contents = \uniqid();
        $this->fixture->dumpFile($relativePath, $contents);
        $this->filesystem->dumpFile($this->getFullPathFromRelativePath($relativePath), $contents)->shouldHaveBeenCalled();
    }

    private function getFullPathFromRelativePath($relativePath) : string
    {
        return "{$this->baseFilesystemPath}/{$relativePath}";
    }

    public function testReadFileReturnsContentsFromFullPathConstructedFromRelativePathIfFileExists() : void
    {
        $this->baseFilesystemPath = \sys_get_temp_dir();
        $this->fixture = new SymfonyFilesystem($this->baseFilesystemPath, $this->filesystem->reveal());

        $relativePath = 'testfile_' . \uniqid() . '.txt';
        $contents = \uniqid();
        \file_put_contents($this->getFullPathFromRelativePath($relativePath), $contents);

        $this->filesystem->exists($this->getFullPathFromRelativePath($relativePath))->willReturn(true);
        self::assertEquals($contents, $this->fixture->readFile($relativePath));

        \unlink($this->getFullPathFromRelativePath($relativePath));
    }

    public function testReadFileThrowsFileNotFoundInFilesystemExceptionIfFullPathConstructedFromRelativePathDoesNotExist() : void
    {
        $relativePath = \uniqid();
        $this->filesystem->exists($this->getFullPathFromRelativePath($relativePath))->willReturn(false);
        $this->expectException(FileNotFoundInFilesystemException::class);
        $this->fixture->readFile($relativePath);
    }

    public function testRemoveRemovesFileInFilesystemFromFullPathConstructedFromRelativePath() : void
    {
        $relativePath = \uniqid();
        $this->fixture->remove($relativePath);
        $this->filesystem->remove($this->getFullPathFromRelativePath($relativePath))->shouldHaveBeenCalled();
    }
}

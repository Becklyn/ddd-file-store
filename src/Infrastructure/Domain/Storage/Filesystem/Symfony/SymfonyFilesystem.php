<?php

namespace Becklyn\FileStore\Infrastructure\Domain\Storage\Filesystem\Symfony;

use Becklyn\FileStore\Domain\Storage\Filesystem\FileNotFoundInFilesystemException;
use Becklyn\FileStore\Domain\Storage\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystemComponent;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since  2020-05-27
 */
class SymfonyFilesystem implements Filesystem
{
    private string $baseFilesystemPath;
    private SymfonyFilesystemComponent $filesystem;

    public function __construct(string $baseFilesystemPath, SymfonyFilesystemComponent $filesystem)
    {
        $this->baseFilesystemPath = $baseFilesystemPath;
        $this->filesystem = $filesystem;
    }

    public function dumpFile(string $relativePathInFilesystem, string $contents): void
    {
        $this->filesystem->dumpFile($this->getFullPath($relativePathInFilesystem), $contents);
    }

    private function getFullPath(string $relativePathInFilesystem): string
    {
        return "{$this->baseFilesystemPath}/$relativePathInFilesystem";
    }

    public function readFile(string $relativePathInFilesystem): string
    {
        $path = $this->getFullPath($relativePathInFilesystem);
        if (!$this->filesystem->exists($path)) {
            throw new FileNotFoundInFilesystemException("File '$relativePathInFilesystem' could not be found in the filesystem");
        }
        return file_get_contents($path);
    }

    public function remove(string $relativePathInFilesystem): void
    {
        $path = $this->getFullPath($relativePathInFilesystem);
        $this->filesystem->remove($path);
    }
}

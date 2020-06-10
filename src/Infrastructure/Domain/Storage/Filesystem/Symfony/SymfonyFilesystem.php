<?php

namespace C201\FileStore\Infrastructure\Domain\Storage\Filesystem\Symfony;

use C201\FileStore\Domain\Storage\Filesystem\FileNotFoundInFilesystemException;
use C201\FileStore\Domain\Storage\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystemComponent;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-05-27
 */
class SymfonyFilesystem implements Filesystem
{
    private string $baseFilesystemPath;
    private SymfonyFilesystemComponent $filesystem;

    public function __construct(string $baseFilesystemPath)
    {
        $this->baseFilesystemPath = $baseFilesystemPath;
        $this->filesystem = new SymfonyFilesystemComponent();
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
}

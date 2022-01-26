<?php

namespace Becklyn\FileStore\Domain\Storage\Filesystem;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since  2020-05-27
 */
interface Filesystem
{
    public function dumpFile(string $relativePathInFilesystem, string $contents): void;

    /**
     * @throws FileNotFoundInFilesystemException
     */
    public function readFile(string $relativePathInFilesystem): string;

    public function remove(string $relativePathInFilesystem): void;
}

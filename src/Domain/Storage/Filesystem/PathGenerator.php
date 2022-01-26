<?php

namespace Becklyn\FileStore\Domain\Storage\Filesystem;

use Ramsey\Uuid\Uuid;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 * @since  2020-05-26
 */
class PathGenerator
{
    public function generate(string $filename = null): string
    {
        $uniqueId = $this->getUniqueId();
        $folderPath = $this->getFolderPath();

        if ($filename === null) {
            return $folderPath . $uniqueId;
        }

        $filename = $this->sanitizeFilename($filename);

        [$filenameWithoutExtension, $extension] = $this->separateFilenameAndExtension($filename);

        $filenameWithoutExtension = substr($filenameWithoutExtension, 0, 20);
        $extension = $extension !== null ? substr($extension, 0, 4) : null;

        // note that the filename itself, without the path through the folders through it, is limited to 255 by the linux filesystem
        // also note that the value returned will likely be stored in a database field limited to 255 characters
        // due to the way this return value is constructed it should never be longer than 69 characters in its entirety
        return $extension ? "$folderPath{$filenameWithoutExtension}_$uniqueId.$extension" : "$folderPath/{$filenameWithoutExtension}_$uniqueId";
    }

    private function getUniqueId(): string
    {
        return str_replace('-', '', Uuid::uuid4());
    }

    private function getFolderPath(): string
    {
        $date = new \DateTime();
        return "{$date->format('Y')}/{$date->format('m')}/{$date->format('d')}/";
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^\w\-.]/', '', $filename);
        $filename = preg_replace('/\.+/', '.', $filename);
        return $filename;
    }

    private function separateFilenameAndExtension(string $filename): array
    {
        $filenameExplosion = explode('.', $filename);
        if (count($filenameExplosion) === 1) {
            return [$filename, null];
        }

        $extension = end($filenameExplosion);
        $filenameWithoutExtension = substr($filename, 0, -strlen(".$extension"));

        return [$filenameWithoutExtension, $extension];
    }
}

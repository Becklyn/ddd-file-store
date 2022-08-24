<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Domain\AbstractCommand;
use Becklyn\Ddd\FileStore\Domain\File\FileId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-28
 */
class DeleteFileCommand extends AbstractCommand
{
    private FileId $fileId;

    public function __construct(FileId $fileId)
    {
        parent::__construct();

        $this->fileId = $fileId;
    }

    public function fileId() : FileId
    {
        return $this->fileId;
    }
}

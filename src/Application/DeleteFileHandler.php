<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Application\CommandHandler;
use Becklyn\Ddd\Commands\Domain\Command;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\FileStore\Domain\File\FileNotFoundException;
use Becklyn\Ddd\FileStore\Domain\FileManager;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-28
 */
class DeleteFileHandler extends CommandHandler
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function handle(DeleteFileCommand $command) : void
    {
        $this->handleCommand($command);
    }

    /**
     * @param DeleteFileCommand $command
     */
    protected function execute(Command $command) : ?EventProvider
    {
        try {
            $file = $this->fileManager->delete($command->fileId(), $command);
        } catch (FileNotFoundException) {
            return null;
        }

        return $file;
    }
}

<?php

namespace Becklyn\FileStore\Application;

use Becklyn\Ddd\Commands\Application\CommandHandler;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\FileStore\Domain\FileManager;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-07-28
 */
class DeleteFileHandler extends CommandHandler
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function handle(DeleteFileCommand $command): void
    {
        $this->handleCommand($command);
    }

    /**
     * @param DeleteFileCommand $command
     */
    protected function execute($command): ?EventProvider
    {
        $this->fileManager->delete($command->fileId());
        return null;
    }
}

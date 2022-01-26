<?php

namespace C201\FileStore\Application;

use C201\Ddd\Commands\Application\CommandHandler;
use C201\Ddd\Events\Domain\EventProvider;
use C201\FileStore\Domain\FileManager;

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
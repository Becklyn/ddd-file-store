<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Application\CommandHandler;
use Becklyn\Ddd\Commands\Domain\Command;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\FileStore\Domain\FileManager;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-05
 */
class ReplaceFileContentsHandler extends CommandHandler
{
    private FileManager $fileManager;
    private ?LoggerInterface $logger;

    public function __construct(FileManager $fileManager, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->fileManager = $fileManager;
    }

    public function handle(ReplaceFileContentsCommand $command) : void
    {
        $this->handleCommand($command);
    }

    /**
     * @param ReplaceFileContentsCommand $command
     */
    protected function execute(Command $command) : ?EventProvider
    {
        return $this->fileManager->replaceContents($command->fileId(), $command->newContents(), $command);
    }

    /**
     * @param ReplaceFileContentsCommand $command
     */
    protected function postRollback(\Throwable $e, Command $command) : \Throwable
    {
        if (null === $this->logger) {
            return $e;
        }

        $message = $command->errorMessage();

        if (null === $message) {
            $message = $command->errorMessage() ?: "Contents of File '{$command->fileId()->asString()}' could not be updated";
        }

        $this->logger->error($message);

        return $e;
    }
}

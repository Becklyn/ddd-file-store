<?php declare(strict_types=1);

namespace Becklyn\FileStore\Application;

use Becklyn\Ddd\Commands\Application\CommandHandler;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\FileStore\Domain\FileManager;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-04
 */
class CreateFileHandler extends CommandHandler
{
    private ?LoggerInterface $logger;
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager, ?LoggerInterface $logger = null)
    {
        $this->fileManager = $fileManager;
        $this->logger = $logger;
    }

    public function handle(CreateFileCommand $command) : void
    {
        $this->handleCommand($command);
    }

    /**
     * @param CreateFileCommand $command
     */
    protected function execute($command) : ?EventProvider
    {
        $file = $this->fileManager->new($command->filename(), $command->contents());
        $file->setOwner($command->ownerId());
        return $file;
    }

    /**
     * @param CreateFileCommand $command
     */
    protected function postRollback(\Throwable $e, $command) : \Throwable
    {
        if (null === $this->logger) {
            return $e;
        }

        $message = $command->errorMessage();

        if (null === $message) {
            $ownerType = \substr(\get_class($command->ownerId()), 0, -2);
            $message = $command->errorMessage() ?: "File '{$command->filename()}' could not be created for {$ownerType} '{$command->ownerId()->asString()}'";
        }

        $this->logger->error($message);

        return $e;
    }
}

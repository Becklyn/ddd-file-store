<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Application\CommandHandler;
use Becklyn\Ddd\Commands\Domain\Command;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\FileStore\Domain\File\FileRepository;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-27
 */
class RenameFileHandler extends CommandHandler
{
    private FileRepository $fileRepository;
    private ?LoggerInterface $logger;

    public function __construct(FileRepository $fileRepository, ?LoggerInterface $logger = null)
    {
        $this->fileRepository = $fileRepository;
        $this->logger = $logger;
    }

    public function handle(RenameFileCommand $command) : void
    {
        $this->handleCommand($command);
    }

    /**
     * @param RenameFileCommand $command
     */
    protected function execute(Command $command) : ?EventProvider
    {
        $file = $this->fileRepository->findOneById($command->fileId());
        $file->rename($command->filename());
        return $file;
    }

    /**
     * @param RenameFileCommand $command
     */
    protected function postRollback(\Throwable $e, Command $command) : \Throwable
    {
        if (null === $this->logger) {
            return $e;
        }

        $message = $command->errorMessage();

        if (null === $message) {
            $message = $command->errorMessage() ?: "File '{$command->fileId()->asString()}' could not be renamed";
        }

        $this->logger->error($message);

        return $e;
    }
}

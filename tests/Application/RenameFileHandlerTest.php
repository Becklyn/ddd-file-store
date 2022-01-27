<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Application;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use Becklyn\Ddd\FileStore\Application\RenameFileCommand;
use Becklyn\Ddd\FileStore\Application\RenameFileHandler;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-27
 *
 * @covers \Becklyn\Ddd\FileStore\Application\RenameFileCommand
 * @covers \Becklyn\Ddd\FileStore\Application\RenameFileHandler
 */
class RenameFileHandlerTest extends TestCase
{
    use FileTestTrait;
    use ProphecyTrait;
    use DomainEventTestTrait;
    use TransactionManagerTestTrait;

    /** @var ObjectProphecy ObjectProphecy|LoggerInterface */
    private ObjectProphecy $logger;
    /**  */
    private RenameFileHandler $fixture;

    protected function setUp() : void
    {
        $this->initFilesTestTrait();
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->fixture = new RenameFileHandler($this->fileRepository->reveal(), $this->logger->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
    }

    public function testFileIsRenamedAndDequeuedByEventRegistry() : void
    {
        $fileId = $this->givenAFileId();
        $filename = $this->givenAFilename();

        $file = $this->givenFileRepositoryFindsFileById($fileId);
        $this->thenFileShouldBeRenamed($file, $filename);
        $this->thenEventRegistryShouldDequeueAndRegister($file->reveal());
        $this->whenRenameFileCommandIsHandled($fileId, $filename);
    }

    /**
     * @param ObjectProphecy|File $file
     */
    private function thenFileShouldBeRenamed(ObjectProphecy $file, string $filename) : void
    {
        $file->rename($filename)->shouldBeCalled();
    }

    private function whenRenameFileCommandIsHandled(FileId $fileId, string $filename, ?string $errorMessage = null) : void
    {
        $this->fixture->handle(new RenameFileCommand($fileId, $filename, $errorMessage));
    }

    public function testExceptionIsThrownIfFileRepositoryThrowsExceptionAndLoggerIsNull() : void
    {
        $fileId = $this->givenAFileId();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->fixture = new RenameFileHandler($this->fileRepository->reveal(), null);
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());

        $this->thenFileNotFoundExceptionShouldBethrown();
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename());
    }

    public function testExceptionIsThrownAndErrorMessageFromCommandIsLoggedIfFileRepositoryThrowsException() : void
    {
        $fileId = $this->givenAFileId();
        $errorMessage = \uniqid();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged($errorMessage);
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename(), $errorMessage);
    }

    private function thenErrorShouldBeLogged($errorMessage) : void
    {
        $this->logger->error($errorMessage)->shouldBeCalled();
    }

    public function testExceptionIsThrownAndErrorMessageIsLoggedIfFileRepositoryThrowsExceptionAndNoErrorMessageIsSpecifiedInCommand() : void
    {
        $fileId = $this->givenAFileId();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged(Argument::any());
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename());
    }
}

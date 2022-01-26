<?php

namespace C201\FileStore\Tests\Application;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use C201\FileStore\Application\RenameFileCommand;
use C201\FileStore\Application\RenameFileHandler;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-07-27
 *
 * @covers \C201\FileStore\Application\RenameFileHandler
 * @covers \C201\FileStore\Application\RenameFileCommand
 */
class RenameFileHandlerTest extends TestCase
{
    use FileTestTrait;
    use ProphecyTrait;
    use DomainEventTestTrait;
    use TransactionManagerTestTrait;

    /** @var ObjectProphecy ObjectProphecy|LoggerInterface */
    private ObjectProphecy $logger;
    /** @var RenameFileHandler */
    private RenameFileHandler $fixture;

    protected function setUp(): void
    {
        $this->initFilesTestTrait();
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->fixture = new RenameFileHandler($this->fileRepository->reveal(), $this->logger->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
    }

    public function testFileIsRenamedAndDequeuedByEventRegistry(): void
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
    private function thenFileShouldBeRenamed(ObjectProphecy $file, string $filename): void
    {
        $file->rename($filename)->shouldBeCalled();
    }

    private function whenRenameFileCommandIsHandled(FileId $fileId, string $filename, string $errorMessage = null): void
    {
        $this->fixture->handle(new RenameFileCommand($fileId, $filename, $errorMessage));
    }

    public function testExceptionIsThrownIfFileRepositoryThrowsExceptionAndLoggerIsNull(): void
    {
        $fileId = $this->givenAFileId();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->fixture = new RenameFileHandler($this->fileRepository->reveal(), null);
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());

        $this->thenFileNotFoundExceptionShouldBethrown();
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename());
    }

    public function testExceptionIsThrownAndErrorMessageFromCommandIsLoggedIfFileRepositoryThrowsException(): void
    {
        $fileId = $this->givenAFileId();
        $errorMessage = uniqid();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged($errorMessage);
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename(), $errorMessage);
    }

    private function thenErrorShouldBeLogged($errorMessage): void
    {
        $this->logger->error($errorMessage)->shouldBeCalled();
    }

    public function testExceptionIsThrownAndErrorMessageIsLoggedIfFileRepositoryThrowsExceptionAndNoErrorMessageIsSpecifiedInCommand(): void
    {
        $fileId = $this->givenAFileId();

        $this->givenFileRepositoryThrowsFileNotFoundExceptionWhileFindingFileById($fileId);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged(Argument::any());
        $this->whenRenameFileCommandIsHandled($fileId, $this->givenAFilename());
    }
}

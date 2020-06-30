<?php

namespace C201\FileStore\Tests\Application;

use C201\Ddd\Events\Domain\DomainEventTestTrait;
use C201\Ddd\Transactions\Application\TransactionManagerTestTrait;
use C201\FileStore\Application\ReplaceFileContentsCommand;
use C201\FileStore\Application\ReplaceFileContentsHandler;
use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;


/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-30
 *
 * @covers \C201\FileStore\Application\ReplaceFileContentsHandler
 * @covers \C201\FileStore\Application\ReplaceFileContentsCommand
 */
class ReplaceFileContentsHandlerTest extends TestCase
{
    use FileTestTrait;
    use ProphecyTrait;
    use DomainEventTestTrait;
    use TransactionManagerTestTrait;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private ObjectProphecy $logger;

    private ReplaceFileContentsHandler $fixture;

    protected function setUp(): void
    {
        $this->initFilesTestTrait();
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->fixture = new ReplaceFileContentsHandler($this->fileManager->reveal(), $this->logger->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
    }

    public function testReplaceFileContentsIsCalledOnFileManagerAndFileWithNewContentsIsDequeuedByEventRegistry(): void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerReplacesContentsForFileWithId($fileId, $contents);
        $this->thenFileWithNewContentsShouldBeDequeuedByEventRegistry($fileId, $contents);
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }

    private function givenFileManagerReplacesContentsForFileWithId(FileId $fileId, string $contents): void
    {
        $this->fileManager->replaceContents($fileId, $contents)->willReturn(File::create($fileId, uniqid(), $contents));
    }

    private function thenFileWithNewContentsShouldBeDequeuedByEventRegistry(FileId $fileId, string $contents): void
    {
        $this->eventRegistry->dequeueProviderAndRegister(Argument::that(fn(File $file) => $file->id()->equals($fileId) && $file->contents() === $contents))
            ->shouldBeCalled();
    }

    private function whenReplaceFileContentsCommandIsHandledForFileIdAndContents(FileId $fileId, string $contents, string $errorMessage = null)
    {
        $this->fixture->handle(new ReplaceFileContentsCommand($fileId, $contents, $errorMessage));
    }

    public function testExceptionIsThrownIfFileManagerThrowsExceptionAndLoggerIsNull(): void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->fixture = new ReplaceFileContentsHandler($this->fileManager->reveal(), null);
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());

        $this->thenFileNotFoundExceptionShouldBethrown();
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }

    private function thenFileNotFoundExceptionShouldBethrown()
    {
        $this->expectException(FileNotFoundException::class);
    }

    public function testExceptionIsThrownAndErrorMessageFromCommandIsLoggedIfFileManagerThrowsException(): void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();
        $errorMessage = uniqid();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->thenFileNotFoundExceptionShouldBethrown();
        $this->thenErrorShouldBeLogged($errorMessage);
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents, $errorMessage);
    }

    private function thenErrorShouldBeLogged($errorMessage): void
    {
        $this->logger->error($errorMessage)->shouldBeCalled();
    }

    public function testExceptionIsThrownAndErrorMessageIsLoggedIfFileManagerThrowsExceptionAndNoErrorMessageIsSpecifiedInCommand(): void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->thenFileNotFoundExceptionShouldBethrown();
        $this->thenErrorShouldBeLogged(Argument::any());
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }
}

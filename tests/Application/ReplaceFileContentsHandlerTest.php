<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Application;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use Becklyn\Ddd\FileStore\Application\ReplaceFileContentsCommand;
use Becklyn\Ddd\FileStore\Application\ReplaceFileContentsHandler;
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
 * @since  2020-06-30
 *
 * @covers \Becklyn\Ddd\FileStore\Application\ReplaceFileContentsCommand
 * @covers \Becklyn\Ddd\FileStore\Application\ReplaceFileContentsHandler
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

    protected function setUp() : void
    {
        $this->initFilesTestTrait();
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->fixture = new ReplaceFileContentsHandler($this->fileManager->reveal(), $this->logger->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
    }

    public function testReplaceFileContentsIsCalledOnFileManagerAndFileWithNewContentsIsDequeuedByEventRegistry() : void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerReplacesContentsForFileWithId($fileId, $contents);
        $this->thenFileWithNewContentsShouldBeDequeuedByEventRegistry($fileId, $contents);
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }

    private function givenFileManagerReplacesContentsForFileWithId(FileId $fileId, string $contents) : void
    {
        $this->fileManager->replaceContents($fileId, $contents)->willReturn(File::create($fileId, \uniqid(), $contents));
    }

    private function thenFileWithNewContentsShouldBeDequeuedByEventRegistry(FileId $fileId, string $contents) : void
    {
        $this->eventRegistry->dequeueProviderAndRegister(Argument::that(fn(File $file) => $file->id()->equals($fileId) && $file->contents() === $contents))
            ->shouldBeCalled();
    }

    private function whenReplaceFileContentsCommandIsHandledForFileIdAndContents(FileId $fileId, string $contents, ?string $errorMessage = null) : void
    {
        $this->fixture->handle(new ReplaceFileContentsCommand($fileId, $contents, $errorMessage));
    }

    public function testExceptionIsThrownIfFileManagerThrowsExceptionAndLoggerIsNull() : void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->fixture = new ReplaceFileContentsHandler($this->fileManager->reveal(), null);
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());

        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }

    public function testExceptionIsThrownAndErrorMessageFromCommandIsLoggedIfFileManagerThrowsException() : void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();
        $errorMessage = \uniqid();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged($errorMessage);
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents, $errorMessage);
    }

    private function thenErrorShouldBeLogged($errorMessage) : void
    {
        $this->logger->error($errorMessage)->shouldBeCalled();
    }

    public function testExceptionIsThrownAndErrorMessageIsLoggedIfFileManagerThrowsExceptionAndNoErrorMessageIsSpecifiedInCommand() : void
    {
        $fileId = $this->givenAFileId();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotFoundExceptionWhileReplacingContentsForFile($fileId, $contents);
        $this->thenFileNotFoundExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged(Argument::any());
        $this->whenReplaceFileContentsCommandIsHandledForFileIdAndContents($fileId, $contents);
    }
}

<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Application;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\FileStore\Application\CreateFileCommand;
use Becklyn\Ddd\FileStore\Application\CreateFileHandler;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotStoredException;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use Becklyn\Ddd\FileStore\Tests\Domain\File\TestProxyAggregateId;
use Becklyn\Ddd\Identity\Domain\AggregateId;
use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Application\CreateFileCommand
 * @covers \Becklyn\Ddd\FileStore\Application\CreateFileHandler
 */
class CreateFileHandlerTest extends TestCase
{
    use FileTestTrait;
    use ProphecyTrait;
    use DomainEventTestTrait;
    use TransactionManagerTestTrait;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private ObjectProphecy $logger;

    private CreateFileHandler $fixture;

    protected function setUp() : void
    {
        $this->initFilesTestTrait();
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->fixture = new CreateFileHandler($this->fileManager->reveal(), $this->logger->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
    }

    public function testNewFileIsCreatedByManagerWithFilenameAndContentsOwnerIsSetAndFileIsDequeuedByEventRegistry() : void
    {
        $filename = $this->givenAFilename();
        $contents = $this->givenFileContents();
        $ownerId = $this->givenAnOwnerId();

        $this->givenFileManagerCreatesNewFileWithFilenameAndContents($filename, $contents);
        $this->thenFileWithFilenameContentsAndOwnerShouldBeDequeuedByEventRegistry($filename, $contents, $ownerId);
        $this->whenCreateFileCommandIsHandledForFilenameContentsAndOwner($filename, $contents, $ownerId);
    }

    private function givenAnOwnerId() : AggregateId
    {
        return TestProxyAggregateId::next();
    }

    private function givenFileManagerCreatesNewFileWithFilenameAndContents(string $filename, string $contents) : void
    {
        $this->fileManager->new($filename, $contents, Argument::any())->willReturn(File::create($this->givenAFileId(), $filename, $contents));
    }

    private function thenFileWithFilenameContentsAndOwnerShouldBeDequeuedByEventRegistry(string $filename, string $contents, AggregateId $ownerId) : void
    {
        $this->eventRegistry->dequeueProviderAndRegister(
            Argument::that(
                fn(File $file) => $file->filename() === $filename &&
                    $file->contents() === $contents &&
                    $ownerId->equals($file->ownerId()) &&
                    $file->ownerType() === \substr(\get_class($ownerId), 0, -2)
            ),
            Argument::any(),
        )->shouldBeCalled();
    }

    private function whenCreateFileCommandIsHandledForFilenameContentsAndOwner(
        string $filename,
        string $contents,
        AggregateId $ownerId,
        ?string $errorMessage = null
    ) : void {
        $this->fixture->handle(new CreateFileCommand($contents, $filename, $ownerId, $errorMessage));
    }

    public function testExceptionIsThrownIfFileManagerThrowsExceptionAndLoggerIsNull() : void
    {
        $filename = $this->givenAFilename();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotStoredExceptionWhileCreatingNewFile($filename, $contents);
        $this->fixture = new CreateFileHandler($this->fileManager->reveal(), null);
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());

        $this->thenFileNotStoredExceptionShouldBeThrown();
        $this->whenCreateFileCommandIsHandledForFilenameContentsAndOwner($filename, $contents, $this->givenAnOwnerId());
    }

    private function thenFileNotStoredExceptionShouldBeThrown() : void
    {
        $this->expectException(FileNotStoredException::class);
    }

    public function testExceptionIsThrownAndErrorMessageFromCommandIsLoggedIfFileManagerThrowsException() : void
    {
        $filename = $this->givenAFilename();
        $contents = $this->givenFileContents();
        $errorMessage = \uniqid();

        $this->givenFileManagerThrowsFileNotStoredExceptionWhileCreatingNewFile($filename, $contents);
        $this->thenFileNotStoredExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged($errorMessage);
        $this->whenCreateFileCommandIsHandledForFilenameContentsAndOwner($filename, $contents, $this->givenAnOwnerId(), $errorMessage);
    }

    private function thenErrorShouldBeLogged($errorMessage) : void
    {
        $this->logger->error($errorMessage)->shouldBeCalled();
    }

    public function testExceptionIsThrownAndErrorMessageIsLoggedIfFileManagerThrowsExceptionAndNoErrorMessageIsSpecifiedInCommand() : void
    {
        $filename = $this->givenAFilename();
        $contents = $this->givenFileContents();

        $this->givenFileManagerThrowsFileNotStoredExceptionWhileCreatingNewFile($filename, $contents);
        $this->thenFileNotStoredExceptionShouldBeThrown();
        $this->thenErrorShouldBeLogged(Argument::any());
        $this->whenCreateFileCommandIsHandledForFilenameContentsAndOwner($filename, $contents, $this->givenAnOwnerId());
    }
}

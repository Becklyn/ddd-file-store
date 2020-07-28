<?php

namespace C201\FileStore\Tests\Application;

use C201\Ddd\Events\Domain\DomainEventTestTrait;
use C201\Ddd\Transactions\Application\TransactionManagerTestTrait;
use C201\FileStore\Application\DeleteFileCommand;
use C201\FileStore\Application\DeleteFileHandler;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-07-28
 *
 * @covers \C201\FileStore\Application\DeleteFileHandler
 * @covers \C201\FileStore\Application\DeleteFileCommand
 */
class DeleteFileHandlerTest extends TestCase
{
    use DomainEventTestTrait;
    use FileTestTrait;
    use ProphecyTrait;
    use TransactionManagerTestTrait;

    private DeleteFileHandler $fixture;

    protected function setUp(): void
    {
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->initFilesTestTrait();
        $this->fixture = new DeleteFileHandler($this->fileManager->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
    }

    public function testFileManagerDeletesFile(): void
    {
        $fileId = $this->givenAFileId();
        $this->thenFileManagerShouldDeleteTheFile($fileId);
        $this->whenDeleteFileCommandIsHandled($fileId);
    }

    private function thenFileManagerShouldDeleteTheFile(FileId $fileId): void
    {
        $this->fileManager->delete($fileId)->shouldBeCalled();
    }

    private function whenDeleteFileCommandIsHandled(FileId $fileId): void
    {
        $this->fixture->handle(new DeleteFileCommand($fileId));
    }
}

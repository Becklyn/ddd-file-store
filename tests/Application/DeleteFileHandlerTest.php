<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Application;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use Becklyn\Ddd\FileStore\Application\DeleteFileCommand;
use Becklyn\Ddd\FileStore\Application\DeleteFileHandler;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-07-28
 *
 * @covers \Becklyn\Ddd\FileStore\Application\DeleteFileCommand
 * @covers \Becklyn\Ddd\FileStore\Application\DeleteFileHandler
 */
class DeleteFileHandlerTest extends TestCase
{
    use DomainEventTestTrait;
    use FileTestTrait;
    use ProphecyTrait;
    use TransactionManagerTestTrait;

    private DeleteFileHandler $fixture;

    protected function setUp() : void
    {
        $this->initDomainEventTestTrait();
        $this->initTransactionManagerTestTrait();
        $this->initFilesTestTrait();
        $this->fixture = new DeleteFileHandler($this->fileManager->reveal());
        $this->fixture->setTransactionManager($this->transactionManager->reveal());
        $this->fixture->setEventRegistry($this->eventRegistry->reveal());
    }

    public function testFileManagerDeletesFile() : void
    {
        $fileId = $this->givenAFileId();
        $this->thenFileManagerShouldDeleteTheFile($fileId);
        $this->whenDeleteFileCommandIsHandled($fileId);
    }

    private function thenFileManagerShouldDeleteTheFile(FileId $fileId) : void
    {
        $this->fileManager->delete($fileId)->shouldBeCalled();
    }

    private function whenDeleteFileCommandIsHandled(FileId $fileId) : void
    {
        $this->fixture->handle(new DeleteFileCommand($fileId));
    }
}

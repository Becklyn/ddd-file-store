<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain;

use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\FileManager;
use Becklyn\Ddd\FileStore\Domain\Storage\Storage;
use Becklyn\Ddd\FileStore\Testing\FileTestTrait;
use Becklyn\Ddd\Messages\Domain\Message;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-29
 *
 * @covers \Becklyn\Ddd\FileStore\Domain\FileManager
 */
class FileManagerTest extends TestCase
{
    use FileTestTrait;
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|Storage
     */
    private ObjectProphecy $storage;

    private FileManager $fixture;

    protected function setUp() : void
    {
        $this->initFilesTestTrait();
        $this->storage = $this->prophesize(Storage::class);

        $this->fixture = new FileManager($this->fileRepository->reveal(), $this->storage->reveal());
    }

    public function testNewReturnsFileWithPassedFilenameAndContentsStoresItAndAddsItToRepository() : void
    {
        $filename = \uniqid();
        $contents = \uniqid();
        $trigger = $this->prophesize(Message::class)->reveal();

        $this->fileRepository->nextIdentity()->willReturn(FileId::next());

        $file = $this->fixture->new($filename, $contents, $trigger);
        self::assertEquals($filename, $file->filename());
        self::assertEquals($contents, $file->contents());

        $this->storage->storeFileContents(
            Argument::that(fn(File $file) => $file->contents() === $contents && $file->filename() === $filename),
            $trigger
        )->shouldHaveBeenCalled();
        $this->fileRepository->add(Argument::that(fn(File $file) => $file->contents() === $contents && $file->filename() === $filename))
            ->shouldHaveBeenCalled();
    }

    public function testLoadReturnsFileWithContentsLoadedFromStorage() : void
    {
        $fileId = $this->givenAFileId();
        $file = $this->givenFileRepositoryFindsOneById($fileId);
        $contents = $this->givenStorageLoadsFileContentsForFile($file->reveal());

        $this->thenContentsShouldBeLoadedIntoFile($file, $contents);
        $this->thenFileWithContentsShouldBeReturned(
            $contents,
            $this->whenLoadIsExecuted($fileId)
        );
    }

    /**
     * @return ObjectProphecy|File
     */
    private function givenFileRepositoryFindsOneById(FileId $fileId) : ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileRepository->findOneById($fileId)->willReturn($file->reveal());
        return $file;
    }

    private function givenStorageLoadsFileContentsForFile(File $file) : string
    {
        $contents = \uniqid();
        $this->storage->loadFileContents($file)->willReturn($contents);
        return $contents;
    }

    /**
     * @param ObjectProphecy|File $file
     */
    private function thenContentsShouldBeLoadedIntoFile(ObjectProphecy $file, string $contents) : void
    {
        $file->load($contents)->shouldBeCalled();
        $file->load($contents)->will(function () use ($file, $contents) {
            $file->contents()->willReturn($contents);
            return $file->reveal();
        });
    }

    private function whenLoadIsExecuted(FileId $fileId)
    {
        return $this->fixture->load($fileId);
    }

    private function thenFileWithContentsShouldBeReturned(string $expectedContents, File $returnedFile) : void
    {
        self::assertEquals($expectedContents, $returnedFile->contents());
    }

    public function testReplaceContentsReturnsFileWithReplacedContentsAndStoresThemToStorageIfNewContentsAreDifferentThanOldOnes() : void
    {
        $fileId = $this->givenAFileId();
        $contents = \uniqid();
        $trigger = $this->prophesize(Message::class)->reveal();

        $file = $this->givenFileRepositoryFindsOneById($fileId);
        $this->givenFileHasContents($file, \uniqid());
        $this->givenFileHasContentHash($file, \uniqid());

        $this->thenContentsShouldBeUpdatedOnFile($file, $contents);
        $this->thenContentsShouldBeStoredForFile($file->reveal(), $trigger);
        $this->thenFileWithContentsShouldBeReturned(
            $contents,
            $this->whenReplaceContentsIsExecuted($fileId, $contents, $trigger)
        );
    }

    /**
     * @param ObjectProphecy|File $file
     */
    private function thenContentsShouldBeUpdatedOnFile(ObjectProphecy $file, string $contents) : void
    {
        $file->updateContents($contents)->shouldBeCalled();
        $file->updateContents($contents)->will(function() use ($file, $contents) {
            $file->contents()->willReturn($contents);
            $file->contentHash()->willReturn(\sha1($contents));
            return $file->reveal();
        });
    }

    private function thenContentsShouldBeStoredForFile(File $file, Message $trigger) : void
    {
        $this->storage->storeFileContents($file, $trigger)->shouldBeCalled();
    }

    private function whenReplaceContentsIsExecuted(FileId $fileId, string $contents, Message $trigger) : File
    {
        return $this->fixture->replaceContents($fileId, $contents, $trigger);
    }

    public function testReplaceContentsReturnsFileAndStoresNoContentsToStorageIfNewContentsAreSameAsExistingOnes() : void
    {
        $fileId = $this->givenAFileId();
        $contents = \uniqid();
        $contentHash = \sha1($contents);
        $trigger = $this->prophesize(Message::class)->reveal();

        $file = $this->givenFileRepositoryFindsOneById($fileId);
        $this->givenFileHasContents($file, $contents);
        $this->givenFileHasContentHash($file, $contentHash);

        $this->thenContentsShouldBeUpdatedOnFile($file, $contents);
        $this->thenContentsShouldNotBeStoredForFile($file->reveal());
        $this->thenFileWithContentsShouldBeReturned(
            $contents,
            $this->whenReplaceContentsIsExecuted($fileId, $contents, $trigger)
        );
    }

    private function thenContentsShouldNotBeStoredForFile(File $file) : void
    {
        $this->storage->storeFileContents($file, Argument::any())->shouldNotBeCalled();
    }

    public function testDeleteRemovesFileFromRepositoryAndDeletesContentsFromStorage() : void
    {
        $fileId = $this->givenAFileId();
        $trigger = $this->prophesize(Message::class)->reveal();

        $file = $this->givenFileRepositoryFindsOneById($fileId);

        $this->thenFileShouldBeRemovedFromRepository($file->reveal());
        $this->thenFileContentsShouldBeDeletedFromStorage($file->reveal(), $trigger);
        $this->whenDeleteIsExecuted($fileId, $trigger);
    }

    private function thenFileShouldBeRemovedFromRepository(File $file) : void
    {
        $this->fileRepository->remove($file)->shouldBeCalled();
    }

    private function thenFileContentsShouldBeDeletedFromStorage(File $file, Message $trigger) : void
    {
        $this->storage->deleteFileContents($file, $trigger)->shouldBeCalled();
    }

    private function whenDeleteIsExecuted(FileId $fileId, Message $trigger) : void
    {
        $this->fixture->delete($fileId, $trigger);
    }
}

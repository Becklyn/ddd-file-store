<?php

namespace C201\FileStore\Tests\Domain;

use C201\FileStore\Domain\File\File;
use C201\FileStore\Domain\File\FileId;
use C201\FileStore\Domain\File\FileNotFoundException;
use C201\FileStore\Domain\File\FileRepository;
use C201\FileStore\Domain\FileManager;
use C201\FileStore\Domain\FileTestTrait;
use C201\FileStore\Domain\Storage\Storage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-29
 *
 * @covers \C201\FileStore\Domain\FileManager
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

    protected function setUp(): void
    {
        $this->initFilesTestTrait();
        $this->storage = $this->prophesize(Storage::class);

        $this->fixture = new FileManager($this->fileRepository->reveal(), $this->storage->reveal());
    }

    public function testNewReturnsFileWithPassedFilenameAndContentsStoresItAndAddsItToRepository(): void
    {
        $filename = uniqid();
        $contents = uniqid();

        $this->fileRepository->nextIdentity()->willReturn(FileId::next());

        $file = $this->fixture->new($filename, $contents);
        $this->assertEquals($filename, $file->filename());
        $this->assertEquals($contents, $file->contents());

        $this->storage->storeFileContents(Argument::that(fn(File $file) => $file->contents() === $contents && $file->filename() === $filename))
            ->shouldHaveBeenCalled();
        $this->fileRepository->add(Argument::that(fn(File $file) => $file->contents() === $contents && $file->filename() === $filename))
            ->shouldHaveBeenCalled();
    }

    public function testLoadReturnsFileWithContentsLoadedFromStorage(): void
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
    private function givenFileRepositoryFindsOneById(FileId $fileId): ObjectProphecy
    {
        /** @var ObjectProphecy|File $file */
        $file = $this->prophesize(File::class);
        $file->id()->willReturn($fileId);
        $this->fileRepository->findOneById($fileId)->willReturn($file->reveal());
        return $file;
    }

    private function givenStorageLoadsFileContentsForFile(File $file): string
    {
        $contents = uniqid();
        $this->storage->loadFileContents($file)->willReturn($contents);
        return $contents;
    }

    /**
     * @param ObjectProphecy|File $file
     */
    private function thenContentsShouldBeLoadedIntoFile(ObjectProphecy $file, string $contents): void
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

    private function thenFileWithContentsShouldBeReturned(string $expectedContents, File $returnedFile): void
    {
        $this->assertEquals($expectedContents, $returnedFile->contents());
    }

    public function testReplaceContentsReturnsFileWithReplacedContentsAndStoresThemToStorageIfNewContentsAreDifferentThanOldOnes(): void
    {
        $fileId = $this->givenAFileId();
        $contents = uniqid();

        $file = $this->givenFileRepositoryFindsOneById($fileId);
        $this->givenFileHasContents($file, uniqid());
        $this->givenFileHasContentHash($file, uniqid());

        $this->thenContentsShouldBeUpdatedOnFile($file, $contents);
        $this->thenContentsShouldBeStoredForFile($file->reveal());
        $this->thenFileWithContentsShouldBeReturned(
            $contents,
            $this->whenReplaceContentsIsExecuted($fileId, $contents)
        );
    }

    /**
     * @param ObjectProphecy|File $file
     */
    private function thenContentsShouldBeUpdatedOnFile(ObjectProphecy $file, string $contents): void
    {
        $file->updateContents($contents)->shouldBeCalled();
        $file->updateContents($contents)->will(function() use ($file, $contents) {
            $file->contents()->willReturn($contents);
            $file->contentHash()->willReturn(sha1($contents));
            return $file->reveal();
        });
    }

    private function thenContentsShouldBeStoredForFile(File $file): void
    {
        $this->storage->storeFileContents($file)->shouldBeCalled();
    }

    private function whenReplaceContentsIsExecuted(FileId $fileId, string $contents): File
    {
        return $this->fixture->replaceContents($fileId, $contents);
    }

    public function testReplaceContentsReturnsFileAndStoresNoContentsToStorageIfNewContentsAreSameAsExistingOnes(): void
    {
        $fileId = $this->givenAFileId();
        $contents = uniqid();
        $contentHash = sha1($contents);

        $file = $this->givenFileRepositoryFindsOneById($fileId);
        $this->givenFileHasContents($file, $contents);
        $this->givenFileHasContentHash($file, $contentHash);

        $this->thenContentsShouldBeUpdatedOnFile($file, $contents);
        $this->thenContentsShouldNotBeStoredForFile($file->reveal());
        $this->thenFileWithContentsShouldBeReturned(
            $contents,
            $this->whenReplaceContentsIsExecuted($fileId, $contents)
        );
    }

    private function thenContentsShouldNotBeStoredForFile(File $file): void
    {
        $this->storage->storeFileContents($file)->shouldNotBeCalled();
    }

    public function testDeleteRemovesFileFromRepositoryAndDeletesContentsFromStorage(): void
    {
        $fileId = $this->givenAFileId();

        $file = $this->givenFileRepositoryFindsOneById($fileId);

        $this->thenFileShouldBeRemovedFromRepository($file->reveal());
        $this->thenFileContentsShouldBeDeletedFromStorage($file->reveal());
        $this->whenDeleteIsExecuted($fileId);
    }

    private function thenFileShouldBeRemovedFromRepository(File $file): void
    {
        $this->fileRepository->remove($file)->shouldBeCalled();
    }

    private function thenFileContentsShouldBeDeletedFromStorage(File $file): void
    {
        $this->storage->deleteFileContents($file)->shouldBeCalled();
    }

    private function whenDeleteIsExecuted(FileId $fileId)
    {
        $this->fixture->delete($fileId);
    }

    public function testDeleteShouldCatchFileNotFoundExceptionAndReturnIfFileIsNotFound(): void
    {
        $fileId = $this->givenAFileId();

        $this->givenFileRepositoryThrowsFileNotFoundException($fileId);
        $this->whenDeleteIsExecuted($fileId);
        $this->thenNoExceptionShouldBeThrownByDelete();
    }

    private function givenFileRepositoryThrowsFileNotFoundException(FileId $fileId)
    {
        $this->fileRepository->findOneById($fileId)->willThrow(new FileNotFoundException());
    }

    private function thenNoExceptionShouldBeThrownByDelete()
    {
        $this->assertTrue(true);
    }
}

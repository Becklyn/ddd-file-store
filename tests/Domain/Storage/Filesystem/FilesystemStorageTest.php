<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Tests\Domain\Storage\Filesystem;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Ddd\FileStore\Domain\File\File;
use Becklyn\Ddd\FileStore\Domain\File\FileId;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotFoundInStorageException;
use Becklyn\Ddd\FileStore\Domain\Storage\FileNotStoredException;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FileNotFoundInFilesystemException;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointer;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerId;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerNotFoundException;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilePointerRepository;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\Filesystem;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilesystemStorage;
use Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\PathGenerator;
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
 * @covers \Becklyn\Ddd\FileStore\Domain\Storage\Filesystem\FilesystemStorage
 */
class FilesystemStorageTest extends TestCase
{
    use DomainEventTestTrait;
    use ProphecyTrait;
    use FileTestTrait;

    /**
     * @var ObjectProphecy|FilePointerRepository
     */
    private ObjectProphecy $filePointerRepository;

    /**
     * @var ObjectProphecy|PathGenerator
     */
    private ObjectProphecy $pathGenerator;

    /**
     * @var ObjectProphecy|Filesystem
     */
    private ObjectProphecy $filesystem;

    private FilesystemStorage $fixture;

    protected function setUp() : void
    {
        $this->initDomainEventTestTrait();
        $this->filePointerRepository = $this->prophesize(FilePointerRepository::class);
        $this->pathGenerator = $this->prophesize(PathGenerator::class);
        $this->filesystem = $this->prophesize(Filesystem::class);

        $this->fixture = new FilesystemStorage(
            $this->eventRegistry->reveal(),
            $this->filePointerRepository->reveal(),
            $this->pathGenerator->reveal(),
            $this->filesystem->reveal()
        );
    }

    public function testStoreFileContentsGeneratesPathForNewFilePointerAddsItToRepositoryAndDequeuesItIfNoPointerIsFoundForFileBeforeDumpingFileContentsToFilePointerPath() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();

        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $filename = $this->givenAFilename();
        $this->givenFileHasFilename($file, $filename);
        $contents = $this->givenFileContents();
        $this->givenFileHasContents($file, $contents);

        $this->givenFilePointerRepositoryThrowsFilePointerNotFoundExceptionForFileId($fileId);
        $path = $this->thenPathShouldBeGeneratedFromFilename($filename);
        $filePointerId = $this->thenFilePointerRepositoryShouldReturnNextId();
        $this->thenNewFilePointerForFileWithGeneratedIdAndPathShouldBeAddedToRepository($filePointerId, $fileId, $path);
        $this->thenNewFilePointerForFileWithGeneratedIdAndPathShouldBeDequeued($filePointerId, $fileId, $path);

        $this->thenContentsForFileShouldBeDumpedForPath($path, $contents);

        $this->whenStoreFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    private function givenFilePointerRepositoryThrowsFilePointerNotFoundExceptionForFileId(FileId $fileId) : void
    {
        $this->filePointerRepository->findOneByFileId($fileId)->willThrow(new FilePointerNotFoundException());
    }

    private function thenPathShouldBeGeneratedFromFilename(string $filename) : string
    {
        $path = \uniqid();
        $this->pathGenerator->generate($filename)->willReturn($path);
        return $path;
    }

    private function thenFilePointerRepositoryShouldReturnNextId() : FilePointerId
    {
        $filePointerId = FilePointerId::next();
        $this->filePointerRepository->nextIdentity()->willReturn($filePointerId);
        return $filePointerId;
    }

    private function thenNewFilePointerForFileWithGeneratedIdAndPathShouldBeAddedToRepository(FilePointerId $filePointerId, FileId $fileId, string $path) : void
    {
        $this->filePointerRepository->add(Argument::that(function(FilePointer $filePointer) use ($filePointerId, $fileId, $path) {
            return $filePointer->id()->equals($filePointerId) && $filePointer->fileId()->equals($fileId) && $filePointer->path() === $path;
        }))->shouldBeCalled();
    }

    private function thenNewFilePointerForFileWithGeneratedIdAndPathShouldBeDequeued(FilePointerId $filePointerId, FileId $fileId, string $path) : void
    {
        $this->eventRegistry->dequeueProviderAndRegister(
            Argument::that(function(FilePointer $filePointer) use ($filePointerId, $fileId, $path) {
                return $filePointer->id()->equals($filePointerId) && $filePointer->fileId()->equals($fileId) && $filePointer->path() === $path;
            }),
            Argument::any()
        )->shouldBeCalled();
    }

    private function thenContentsForFileShouldBeDumpedForPath(string $path, string $contents) : void
    {
        $this->filesystem->dumpFile($path, $contents)->shouldBeCalled();
    }

    private function whenStoreFileContentsIsExecutedForFile(File $file, Message $trigger) : void
    {
        $this->fixture->storeFileContents($file, $trigger);
    }

    public function testStoreFileContentsDumpsFileContentsToPathFromFilePointerIfPointerCanBeFoundForFile() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();

        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $contents = $this->givenFileContents();
        $this->givenFileHasContents($file, $contents);

        $filePointer = $this->givenFilePointerRepositoryFindsOneByFileId($fileId);
        $path = $this->givenAPath();
        $this->givenFilePointerHasPath($filePointer, $path);

        $this->thenContentsForFileShouldBeDumpedForPath($path, $contents);

        $this->whenStoreFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    /**
     * @return ObjectProphecy|FilePointer
     */
    private function givenFilePointerRepositoryFindsOneByFileId(FileId $fileId) : ObjectProphecy
    {
        /** @var ObjectProphecy|FilePointer $filePointer */
        $filePointer = $this->prophesize(FilePointer::class);
        $filePointer->fileId()->willReturn($fileId);
        $this->filePointerRepository->findOneByFileId($fileId)->willReturn($filePointer->reveal());
        return $filePointer;
    }

    private function givenAPath() : string
    {
        return \uniqid();
    }

    /**
     * @param ObjectProphecy|FilePointer $filePointer
     */
    private function givenFilePointerHasPath(ObjectProphecy $filePointer, string $path) : void
    {
        $filePointer->path()->willReturn($path);
    }

    public function testStoreFileContentsThrowsFileNotStoredExceptionIfFilesystemThrowsExceptionWhenDumpingFileContentsToPath() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();

        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $contents = $this->givenFileContents();
        $this->givenFileHasContents($file, $contents);

        $filePointer = $this->givenFilePointerRepositoryFindsOneByFileId($fileId);
        $path = $this->givenAPath();
        $this->givenFilePointerHasPath($filePointer, $path);

        $this->givenFilesystemThrowsExceptionWhenDumpingFileContentsToPath($path, $contents);

        $this->thenFileNotStoredExceptionShouldBeThrown();

        $this->whenStoreFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    private function givenFilesystemThrowsExceptionWhenDumpingFileContentsToPath(string $path, string $contents) : void
    {
        $this->filesystem->dumpFile($path, $contents)->willThrow(new \Exception());
    }

    private function thenFileNotStoredExceptionShouldBeThrown() : void
    {
        $this->expectException(FileNotStoredException::class);
    }

    public function testLoadFileContentsReturnsDataReadByFilesystemFromPathInFilePointerFoundForFile() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();

        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);

        $filePointer = $this->givenFilePointerRepositoryFindsOneByFileId($fileId);
        $path = $this->givenAPath();
        $this->givenFilePointerHasPath($filePointer, $path);

        $data = $this->givenFilesystemReadFileReturnsDataReadFromPath($path);

        $this->thenDataShouldBeReturned(
            $data,
            $this->whenLoadFileContentsIsExecutedForFile($file->reveal(), $trigger)
        );
    }

    private function givenFilesystemReadFileReturnsDataReadFromPath(string $path) : string
    {
        $data = \uniqid();
        $this->filesystem->readFile($path)->willReturn($data);
        return $data;
    }

    private function thenDataShouldBeReturned(string $expected, string $actual) : void
    {
        self::assertEquals($expected, $actual);
    }

    private function whenLoadFileContentsIsExecutedForFile(File $file) : string
    {
        return $this->fixture->loadFileContents($file);
    }

    public function testLoadFileContentsThrowsFileNotFoundInStorageExceptionWhenFilePointerRepositoryThrowsFilePointerNotFoundExceptionForFile() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();
        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $this->givenFilePointerRepositoryThrowsFilePointerNotFoundExceptionForFileId($fileId);
        $this->thenFileNotFoundInStorageExceptionShouldBeThrown();
        $this->whenLoadFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    private function thenFileNotFoundInStorageExceptionShouldBeThrown() : void
    {
        $this->expectException(FileNotFoundInStorageException::class);
    }

    public function testLoadFileContentsThrowsFileNotFoundInStorageExceptionWhenFilesystemThrowsFileNotFoundInFilesystemException() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();
        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $filePointer = $this->givenFilePointerRepositoryFindsOneByFileId($fileId);
        $path = $this->givenAPath();
        $this->givenFilePointerHasPath($filePointer, $path);
        $this->givenFilesystemThrowsFileNotFoundInFilesystemExceptionForPathFromFilePointer($path);
        $this->thenFileNotFoundInStorageExceptionShouldBeThrown();
        $this->whenLoadFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    private function givenFilesystemThrowsFileNotFoundInFilesystemExceptionForPathFromFilePointer(string $path) : void
    {
        $this->filesystem->readFile($path)->willThrow(new FileNotFoundInFilesystemException());
    }

    public function testDeleteFileContentsRemovesFilePointerFromRepositoryAndFilesystemRemovesDataFromFilePointerPath() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();

        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);

        $filePointer = $this->givenFilePointerRepositoryFindsOneByFileId($fileId);
        $path = $this->givenAPath();
        $this->givenFilePointerHasPath($filePointer, $path);

        $this->thenFilePointerShouldBeRemovedFromRepository($filePointer->reveal());
        $this->thenFilesystemShouldRemoveDataFromFilePointerPath($path);
        $this->whenDeleteFileContentsIsExecutedForFile($file->reveal(), $trigger);
    }

    private function thenFilePointerShouldBeRemovedFromRepository(FilePointer $filePointer) : void
    {
        $this->filePointerRepository->remove($filePointer)->shouldBeCalled();
    }

    private function thenFilesystemShouldRemoveDataFromFilePointerPath(string $path) : void
    {
        $this->filesystem->remove($path)->shouldBeCalled();
    }

    private function whenDeleteFileContentsIsExecutedForFile(File $file, Message $trigger) : void
    {
        $this->fixture->deleteFileContents($file, $trigger);
    }

    public function testDeleteFileContentsCatchesFilePointerNotFoundExceptionThrownByFilePointerRepositoryAndReturns() : void
    {
        $trigger = $this->prophesize(Message::class)->reveal();
        $fileId = $this->givenAFileId();
        $file = $this->givenAFileWithId($fileId);
        $this->givenFilePointerRepositoryThrowsFilePointerNotFoundExceptionForFileId($fileId);
        $this->whenDeleteFileContentsIsExecutedForFile($file->reveal(), $trigger);
        $this->thenNoExceptionShouldBubble();
    }

    private function thenNoExceptionShouldBubble() : void
    {
        self::assertTrue(true);
    }
}

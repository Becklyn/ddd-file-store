<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Domain\File;

use Becklyn\Ddd\Identity\Domain\AggregateId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-05-26
 */
interface FileRepository
{
    public function nextIdentity() : FileId;

    public function add(File $file) : void;

    /**
     * @throws FileNotFoundException
     */
    public function findOneById(FileId $fileId) : File;

    public function remove(File $file) : void;

    /**
     * @throws FileNotFoundException
     */
    public function findOneByOwnerId(AggregateId $ownerId) : File;
}

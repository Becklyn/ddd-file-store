<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Application;

use Becklyn\Ddd\Commands\Domain\AbstractCommand;
use Becklyn\Ddd\Identity\Domain\AggregateId;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-04
 */
class CreateFileCommand extends AbstractCommand
{
    private string $contents;
    private string $filename;
    private AggregateId $ownerId;
    private ?string $errorMessage;

    public function __construct(string $contents, string $filename, AggregateId $ownerId, ?string $errorMessage = null)
    {
        parent::__construct();

        $this->contents = $contents;
        $this->filename = $filename;
        $this->ownerId = $ownerId;
        $this->errorMessage = $errorMessage;
    }

    public function contents() : string
    {
        return $this->contents;
    }

    public function filename() : string
    {
        return $this->filename;
    }

    public function ownerId() : AggregateId
    {
        return $this->ownerId;
    }

    public function errorMessage() : ?string
    {
        return $this->errorMessage;
    }
}

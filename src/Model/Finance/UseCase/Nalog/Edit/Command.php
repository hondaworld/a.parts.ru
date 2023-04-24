<?php

namespace App\Model\Finance\UseCase\Nalog\Edit;

use App\Model\Finance\Entity\Nalog\Nalog;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $nalogID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $nalogID)
    {
        $this->nalogID = $nalogID;
    }

    public static function fromNalog(Nalog $nalog): self
    {
        $command = new self($nalog->getId());
        $command->name = $nalog->getName();
        return $command;
    }
}

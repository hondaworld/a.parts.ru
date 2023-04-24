<?php

namespace App\Model\User\UseCase\Opt\Edit;

use App\Model\User\Entity\Opt\Opt;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $optID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $optID)
    {
        $this->optID = $optID;
    }

    public static function fromEntity(Opt $opt): self
    {
        $command = new self($opt->getId());
        $command->name = $opt->getName();
        return $command;
    }
}

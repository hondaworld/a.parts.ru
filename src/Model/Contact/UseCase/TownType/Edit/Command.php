<?php

namespace App\Model\Contact\UseCase\TownType\Edit;

use App\Model\Contact\Entity\TownType\TownType;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name_short;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromType(TownType $type): self
    {
        $command = new self($type->getId());
        $command->name = $type->getName();
        $command->name_short = $type->getNameShort();
        return $command;
    }
}

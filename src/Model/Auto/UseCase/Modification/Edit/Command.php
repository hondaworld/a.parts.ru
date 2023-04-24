<?php

namespace App\Model\Auto\UseCase\Modification\Edit;

use App\Model\Auto\Entity\Modification\AutoModification;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_modificationID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $auto_modificationID)
    {
        $this->auto_modificationID = $auto_modificationID;
    }

    public static function fromEntity(AutoModification $autoModification): self
    {
        $command = new self($autoModification->getId());
        $command->name = $autoModification->getName();
        return $command;
    }
}

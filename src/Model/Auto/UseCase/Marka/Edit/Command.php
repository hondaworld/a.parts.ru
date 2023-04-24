<?php

namespace App\Model\Auto\UseCase\Marka\Edit;

use App\Model\Auto\Entity\Marka\AutoMarka;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_markaID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $name_rus;

    public function __construct(int $auto_markaID)
    {
        $this->auto_markaID = $auto_markaID;
    }

    public static function fromEntity(AutoMarka $autoMarka): self
    {
        $command = new self($autoMarka->getId());
        $command->auto_markaID = $autoMarka->getId();
        $command->name = $autoMarka->getName();
        $command->name_rus = $autoMarka->getNameRus();
        return $command;
    }
}

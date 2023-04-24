<?php

namespace App\Model\Auto\UseCase\Generation\DescriptionSpare;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_generationID;

    public $spare;

    public function __construct(int $auto_generationID)
    {
        $this->auto_generationID = $auto_generationID;
    }

    public static function fromEntity(AutoGeneration $autoGeneration): self
    {
        $command = new self($autoGeneration->getId());
        $command->spare = $autoGeneration->getDescription()->getSpare();
        return $command;
    }
}

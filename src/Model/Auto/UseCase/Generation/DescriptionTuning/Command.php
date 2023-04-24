<?php

namespace App\Model\Auto\UseCase\Generation\DescriptionTuning;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_generationID;

    public $tuning;

    public function __construct(int $auto_generationID)
    {
        $this->auto_generationID = $auto_generationID;
    }

    public static function fromEntity(AutoGeneration $autoGeneration): self
    {
        $command = new self($autoGeneration->getId());
        $command->tuning = $autoGeneration->getDescription()->getTuning();
        return $command;
    }
}

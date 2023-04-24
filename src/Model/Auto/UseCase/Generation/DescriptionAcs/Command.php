<?php

namespace App\Model\Auto\UseCase\Generation\DescriptionAcs;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_generationID;

    public $acs;

    public function __construct(int $auto_generationID)
    {
        $this->auto_generationID = $auto_generationID;
    }

    public static function fromEntity(AutoGeneration $autoGeneration): self
    {
        $command = new self($autoGeneration->getId());
        $command->acs = $autoGeneration->getDescription()->getAcs();
        return $command;
    }
}

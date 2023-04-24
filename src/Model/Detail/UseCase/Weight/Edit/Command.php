<?php

namespace App\Model\Detail\UseCase\Weight\Edit;

use App\Model\Detail\Entity\Weight\Weight;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $weightID;

    /**
     * @Assert\NotBlank()
     */
    public $weight;

    public $weightIsReal;

    public function __construct(int $weightID)
    {
        $this->weightID = $weightID;
    }

    public static function fromEntity(Weight $weight): self
    {
        $command = new self($weight->getId());
        $command->weight = $weight->getWeight();
        $command->weightIsReal = $weight->getWeightIsReal();
        return $command;
    }
}

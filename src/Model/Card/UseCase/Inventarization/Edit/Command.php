<?php

namespace App\Model\Card\UseCase\Inventarization\Edit;

use App\Model\Card\Entity\Inventarization\Inventarization;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $inventarizationID;
    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public $isClose;

    public function __construct(int $inventarizationID)
    {
        $this->inventarizationID = $inventarizationID;
    }

    public static function fromEntity(Inventarization $inventarization): self
    {
        $command = new self($inventarization->getId());
        $command->dateofadded = $inventarization->getDateofadded();
        return $command;
    }
}

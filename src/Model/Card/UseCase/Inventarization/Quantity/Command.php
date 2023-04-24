<?php

namespace App\Model\Card\UseCase\Inventarization\Quantity;

use App\Model\Card\Entity\Inventarization\InventarizationGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $goodID;

    /**
     * @Assert\NotBlank()
     */
    public $quantity_real;

    public function __construct(int $goodID)
    {
        $this->goodID = $goodID;
    }

    public static function fromEntity(InventarizationGood $inventarizationGood): self
    {
        $command = new self($inventarizationGood->getId());
        $command->quantity_real = $inventarizationGood->getQuantityReal();
        return $command;
    }
}

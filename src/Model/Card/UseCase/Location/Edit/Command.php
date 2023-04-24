<?php

namespace App\Model\Card\UseCase\Location\Edit;

use App\Model\Card\Entity\Location\ZapSkladLocation;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapSkladLocationID;

    public $locationID;

    /**
     * @Assert\NotBlank()
     */
    public $quantityMin;

    public $quantityMinIsReal;

    /**
     * @Assert\NotBlank()
     */
    public $quantityMax;

    public function __construct(int $zapSkladLocationID)
    {
        $this->zapSkladLocationID = $zapSkladLocationID;
    }

    public static function fromEntity(ZapSkladLocation $zapSkladLocation): self
    {
        $command = new self($zapSkladLocation->getId());
        $command->locationID = $zapSkladLocation->getLocation() ? $zapSkladLocation->getLocation()->getId() : null;
        $command->quantityMin = $zapSkladLocation->getQuantityMin();
        $command->quantityMinIsReal = $zapSkladLocation->getQuantityMinIsReal();
        $command->quantityMax = $zapSkladLocation->getQuantityMax();
        return $command;
    }
}

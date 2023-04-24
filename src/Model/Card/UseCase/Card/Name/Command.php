<?php

namespace App\Model\Card\UseCase\Card\Name;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $zapGroupID;

    public $name;

    public $description;

    public $name_big;

    public $nameEng;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->name = $zapCard->getName();
        $command->zapGroupID = $zapCard->getZapGroup() != null ? $zapCard->getZapGroup()->getId() : null;
        $command->description = $zapCard->getDescription();
        $command->name_big = $zapCard->getNameBig();
        $command->nameEng = $zapCard->getNameEng();
        return $command;
    }
}

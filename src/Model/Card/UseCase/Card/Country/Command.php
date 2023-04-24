<?php

namespace App\Model\Card\UseCase\Card\Country;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $countryID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->countryID = $zapCard->getManager() != null ? $zapCard->getManager()->getId() : null;
        return $command;
    }
}

<?php

namespace App\Model\Card\UseCase\Card\Dop;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @Assert\NotBlank()
     */
    public $shop_typeID;

    /**
     * @Assert\NotBlank()
     */
    public $ed_izmID;

    public $countryID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->name = $zapCard->getName();
        $command->shop_typeID = $zapCard->getShopType() != null ? $zapCard->getShopType()->getId() : null;
        $command->ed_izmID = $zapCard->getEdIzm() != null ? $zapCard->getEdIzm()->getId() : null;
        $command->countryID = $zapCard->getManager() != null ? $zapCard->getManager()->getId() : null;
        return $command;
    }
}

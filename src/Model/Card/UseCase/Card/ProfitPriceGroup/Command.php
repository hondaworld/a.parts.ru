<?php

namespace App\Model\Card\UseCase\Card\ProfitPriceGroup;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @var boolean
     */
    public $is_price_group_fix;

    /**
     * @var int
     */
    public $price_groupID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->is_price_group_fix = $zapCard->isPriceGroupFix();
        $command->price_groupID = $zapCard->getPriceGroup() ? $zapCard->getPriceGroup()->getId() : null;
        return $command;
    }
}

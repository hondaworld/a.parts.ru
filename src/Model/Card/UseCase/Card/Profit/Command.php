<?php

namespace App\Model\Card\UseCase\Card\Profit;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $price1;

    public $profit;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->price1 = $zapCard->getPrice1();
        $command->profit = $zapCard->getProfit();
        return $command;
    }
}

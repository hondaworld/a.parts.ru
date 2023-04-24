<?php

namespace App\Model\Card\UseCase\Card\Stock;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @var int
     */
    public $numberID;

    /**
     * @var string
     */
    public $price_stock;

    /**
     * @var int
     */
    public $stockID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard, ?ZapCardStockNumber $stockNumber): self
    {
        $command = new self($zapCard->getId());
        if ($stockNumber) {
            $command->numberID = $stockNumber->getId();
            $command->price_stock = $stockNumber->getPriceStock();
            $command->stockID = $stockNumber->getStock()->getId();
        }
        return $command;
    }
}

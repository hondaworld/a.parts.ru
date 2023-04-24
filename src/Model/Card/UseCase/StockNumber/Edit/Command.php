<?php

namespace App\Model\Card\UseCase\StockNumber\Edit;

use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $numberID;

    /**
     * @var string
     */
    public $price_stock;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $stockID;

    public function __construct(int $numberID)
    {
        $this->numberID = $numberID;
    }

    public static function fromEntity(ZapCardStockNumber $stockNumber): self
    {
        $command = new self($stockNumber->getId());
        $command->price_stock = $stockNumber->getPriceStock();
        $command->stockID = $stockNumber->getStock()->getId();
        return $command;
    }
}

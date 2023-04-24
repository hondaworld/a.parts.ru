<?php

namespace App\Model\Card\UseCase\StockNumber\Create;

use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $numbers;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $price_stock;

    /**
     * @var ZapCardStock
     */
    public $stock;

    public function __construct(ZapCardStock $zapCardStock)
    {
        $this->stock = $zapCardStock;
    }
}

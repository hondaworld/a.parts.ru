<?php

namespace App\Model\Card\UseCase\StockNumber\Upload;

use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @var string
     */
    public $file;

    /**
     * @var ZapCardStock
     */
    public $stock;

    public function __construct(ZapCardStock $zapCardStock)
    {
        $this->stock = $zapCardStock;
    }
}

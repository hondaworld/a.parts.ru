<?php

namespace App\Model\Detail\UseCase\Dealer\Edit;

use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $shopPriceDealerID;

    /**
     * @Assert\NotBlank()
     */
    public $price;

    public function __construct(int $shopPriceDealerID)
    {
        $this->shopPriceDealerID = $shopPriceDealerID;
    }

    public static function fromEntity(ShopPriceDealer $shopPriceDealer): self
    {
        $command = new self($shopPriceDealer->getId());
        $command->price = $shopPriceDealer->getPrice();
        return $command;
    }
}

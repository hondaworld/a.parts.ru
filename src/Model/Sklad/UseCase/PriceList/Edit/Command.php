<?php

namespace App\Model\Sklad\UseCase\PriceList\Edit;

use App\Model\Sklad\Entity\PriceList\PriceList;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $price_listID;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $koef_dealer;

    /**
     * @var boolean
     */
    public $no_discount;

    /**
     * @var boolean
     */
    public $isMain;

    public function __construct(int $price_listID)
    {
        $this->price_listID = $price_listID;
    }

    public static function fromEntity(PriceList $priceList): self
    {
        $command = new self($priceList->getId());
        $command->name = $priceList->getName();
        $command->koef_dealer = $priceList->getKoefDealer();
        $command->no_discount = $priceList->getNoDiscount();
        $command->isMain = $priceList->isMain();
        return $command;
    }
}

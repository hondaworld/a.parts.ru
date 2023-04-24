<?php

namespace App\Model\User\UseCase\ShopPayType\Edit;

use App\Model\User\Entity\ShopPayType\ShopPayType;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $shop_pay_typeID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $shop_pay_typeID)
    {
        $this->shop_pay_typeID = $shop_pay_typeID;
    }

    public static function fromEntity(ShopPayType $shopPayType): self
    {
        $command = new self($shopPayType->getId());
        $command->name = $shopPayType->getName();
        return $command;
    }
}

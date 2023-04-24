<?php

namespace App\Model\Shop\UseCase\ShopType\Edit;

use App\Model\Shop\Entity\ShopType\ShopType;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $shop_typeID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $shop_typeID) {
        $this->shop_typeID = $shop_typeID;
    }

    public static function fromDocument(ShopType $shopType): self
    {
        $command = new self($shopType->getId());
        $command->name = $shopType->getName();
        return $command;
    }
}

<?php

namespace App\Model\Sklad\UseCase\PriceGroup\Edit;

use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceList\PriceList;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $price_groupID;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var boolean
     */
    public $isMain;

    public function __construct(int $price_groupID)
    {
        $this->price_groupID = $price_groupID;
    }

    public static function fromEntity(PriceGroup $priceGroup): self
    {
        $command = new self($priceGroup->getId());
        $command->name = $priceGroup->getName();
        $command->isMain = $priceGroup->isMain();
        return $command;
    }
}

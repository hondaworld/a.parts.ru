<?php

namespace App\Model\Card\UseCase\Card\Create;

use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
     *     maxMessage="Номер должен быть не больше 30 символов"
     * )
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @Assert\NotBlank()
     */
    public $shop_typeID;

    /**
     * @Assert\NotBlank()
     */
    public $zapGroupID;

    public $name;

    public $description;

    /**
     * @Assert\NotBlank()
     */
    public $price_groupID;

    public function __construct()
    {
        $this->price_groupID = PriceGroup::DEFAULT_ID;
    }
}

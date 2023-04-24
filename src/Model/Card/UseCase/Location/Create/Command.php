<?php

namespace App\Model\Card\UseCase\Location\Create;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $zapCardID;

    /**
     * @Assert\NotBlank()
     */
    public $zapSkladID;

    public $locationID;

    /**
     * @Assert\NotBlank()
     */
    public $quantityMin;

    public $quantityMinIsReal;

    /**
     * @Assert\NotBlank()
     */
    public $quantityMax;

    public function __construct(ZapCard $zapCard)
    {
        $this->zapCardID = $zapCard->getId();
    }
}

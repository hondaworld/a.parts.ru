<?php

namespace App\Model\Card\UseCase\Zamena\Create;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $zapCard;

    /**
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    public function __construct(ZapCard $zapCard)
    {
        $this->zapCard = $zapCard;
    }
}

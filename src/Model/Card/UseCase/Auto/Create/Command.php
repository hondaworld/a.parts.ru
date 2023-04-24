<?php

namespace App\Model\Card\UseCase\Auto\Create;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $zapCard;

    /**
     * @Assert\NotBlank()
     */
    public $year;

    public $moto_modelID;

    public $auto_modelID;

    public function __construct(ZapCard $zapCard)
    {
        $this->zapCard = $zapCard;
    }
}

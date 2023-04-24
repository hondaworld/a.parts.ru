<?php

namespace App\Model\Card\UseCase\Inventarization\QuantityScan;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $quantity_real;
}

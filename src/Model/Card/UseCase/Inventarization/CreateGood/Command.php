<?php

namespace App\Model\Card\UseCase\Inventarization\CreateGood;

use App\Model\Card\Entity\Inventarization\InventarizationGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $quantity_real;

    /**
     * @Assert\NotBlank()
     */

    public $zapCardID;

    /**
     * @Assert\NotBlank()
     */
    public $zapSkladID;
}

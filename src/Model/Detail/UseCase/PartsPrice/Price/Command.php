<?php

namespace App\Model\Detail\UseCase\PartsPrice\Price;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @Assert\NotBlank()
     */
    public $providerPriceID;

    /**
     * @Assert\NotBlank()
     */
    public $price;
}

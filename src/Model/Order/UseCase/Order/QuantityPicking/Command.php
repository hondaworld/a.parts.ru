<?php

namespace App\Model\Order\UseCase\Order\QuantityPicking;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $quantityPicking;
}

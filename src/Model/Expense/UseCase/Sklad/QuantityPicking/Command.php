<?php

namespace App\Model\Expense\UseCase\Sklad\QuantityPicking;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $quantityPicking;
}

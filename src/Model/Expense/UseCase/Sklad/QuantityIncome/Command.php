<?php

namespace App\Model\Expense\UseCase\Sklad\QuantityIncome;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $quantityIncome;
}

<?php

namespace App\Model\Expense\UseCase\Shipping\Status;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, статус"
     * )
     */
    public $status;

    public $cols;
}

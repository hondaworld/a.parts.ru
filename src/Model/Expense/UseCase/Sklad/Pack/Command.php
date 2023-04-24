<?php

namespace App\Model\Expense\UseCase\Sklad\Pack;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     */
    public $managerID;

    public $isDelete;

    public $cols;
}

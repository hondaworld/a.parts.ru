<?php

namespace App\Model\Finance\UseCase\FinanceType\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank
     */
    public $name;

    /**
     * @var int
     * @Assert\NotBlank
     */
    public $firmID;

    public $isMain;
}

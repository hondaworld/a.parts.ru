<?php

namespace App\Model\Provider\UseCase\Provider\BalanceHistory\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $firmID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $balance;

    /**
     * @var string
     */
    public $balance_nds;
}

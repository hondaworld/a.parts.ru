<?php

namespace App\Model\Firm\UseCase\BalanceHistory\Create;

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
    public $providerID;

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

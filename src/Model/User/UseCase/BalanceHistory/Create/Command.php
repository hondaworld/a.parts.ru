<?php

namespace App\Model\User\UseCase\BalanceHistory\Create;

use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
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
     * @var int
     * @Assert\NotBlank()
     */
    public $finance_typeID;

    /**
     * @var int
     */
    public $schetID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $balance;

    /**
     * @var bool
     */
    public $isSend;
}

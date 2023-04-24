<?php

namespace App\Model\User\UseCase\BalanceHistory\EditFinanceType;

use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $balanceID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $finance_typeID;

    public function __construct(int $balanceID)
    {
        $this->balanceID = $balanceID;
    }

    public static function fromEntity(UserBalanceHistory $userBalanceHistory): self
    {
        $command = new self($userBalanceHistory->getId());
        $command->firmID = $userBalanceHistory->getFirm()->getId();
        $command->finance_typeID = $userBalanceHistory->getFinanceType()->getId();
        return $command;
    }
}

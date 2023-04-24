<?php

namespace App\Model\User\UseCase\User\Debt;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var string
     */
    public $balanceLimit;

    /**
     * @var int
     */
    public $debts_days;

    /**
     * @var int
     */
    public $debtInDays;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->balanceLimit = $user->getBalanceLimit();
        $command->debts_days = $user->getDebt()->getDebtsDays();
        $command->debtInDays = $user->getDebt()->getDebtInDays();
        return $command;
    }
}

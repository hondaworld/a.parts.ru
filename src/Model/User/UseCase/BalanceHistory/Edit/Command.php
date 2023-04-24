<?php

namespace App\Model\User\UseCase\BalanceHistory\Edit;

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
     * @var string
     */
    public $description;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $balance;

    public function __construct(int $balanceID)
    {
        $this->balanceID = $balanceID;
    }

    public static function fromEntity(UserBalanceHistory $userBalanceHistory): self
    {
        $command = new self($userBalanceHistory->getId());
        $command->description = $userBalanceHistory->getDescription();
        $command->firmID = $userBalanceHistory->getFirm()->getId();
        $command->balance = $userBalanceHistory->getBalance();
        return $command;
    }
}

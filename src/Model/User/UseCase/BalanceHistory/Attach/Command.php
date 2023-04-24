<?php

namespace App\Model\User\UseCase\BalanceHistory\Attach;

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
     */
    public $userID;

    /**
     * @var string
     */
    public $attach;

    public function __construct(int $balanceID)
    {
        $this->balanceID = $balanceID;
    }

    public static function fromEntity(UserBalanceHistory $userBalanceHistory, string $attachDirectory): self
    {
        $command = new self($userBalanceHistory->getId());
        $command->userID = $userBalanceHistory->getUser()->getId();
        $command->attach = $userBalanceHistory->getAttach() ? $attachDirectory . $userBalanceHistory->getAttach() : '';
        return $command;
    }
}

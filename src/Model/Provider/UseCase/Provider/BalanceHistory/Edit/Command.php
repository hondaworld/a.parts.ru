<?php

namespace App\Model\Provider\UseCase\Provider\BalanceHistory\Edit;

use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
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
    public $firmID;

    /**
     * @var \DateTime
     */
    public $dateofadded;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $balance;

    /**
     * @var string
     */
    public $balance_nds;

    public function __construct(int $balanceID)
    {
        $this->balanceID = $balanceID;
    }

    public static function fromEntity(FirmBalanceHistory $firmBalanceHistory): self
    {
        $command = new self($firmBalanceHistory->getId());
        $command->firmID = $firmBalanceHistory->getFirm()->getId();
        $command->description = $firmBalanceHistory->getDescription();
        $command->balance = $firmBalanceHistory->getBalance();
        $command->balance_nds = $firmBalanceHistory->getBalanceNds();
        $command->dateofadded = $firmBalanceHistory->getDateofadded();
        return $command;
    }
}

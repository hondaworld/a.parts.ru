<?php

namespace App\Model\Finance\UseCase\FinanceType\Edit;

use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\UseCase\Contact\Address;
use App\Model\Contact\UseCase\Contact\Town;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $finance_typeID;

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
    public function __construct(int $finance_typeID) {
        $this->finance_typeID = $finance_typeID;
    }

    public static function fromEntity(FinanceType $financeType): self
    {
        $command = new self($financeType->getId());
        $command->name = $financeType->getName();
        $command->firmID = $financeType->getFirm()->getId();
        $command->isMain = $financeType->isMain();
        return $command;
    }
}

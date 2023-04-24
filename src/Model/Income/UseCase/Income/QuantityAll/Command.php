<?php

namespace App\Model\Income\UseCase\Income\QuantityAll;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $incomeID;

    /**
     * @var array
     */
    public $sklads;

    public $quantity;

    public $quantityIn;

    public $quantityPath;

    public $reserve;

    public $quantityReturn;

    /**
     * @var array
     */
    public $incomeSklads;

    public function __construct(int $incomeID)
    {
        $this->incomeID = $incomeID;
    }

    public static function fromEntity(Income $income, array $sklads, array $incomeSklads): self
    {
        $command = new self($income->getId());
        $command->sklads = $sklads;
        $command->incomeSklads = $incomeSklads;
        $command->quantity = $income->getQuantity();
        $command->quantityIn = $income->getQuantityIn();
        $command->quantityPath = $income->getQuantityPath();
        $command->reserve = $income->getReserve();
        $command->quantityReturn = $income->getQuantityReturn();
        return $command;
    }

    public function getQuantity(int $zapSkladID)
    {
        return 'quantity_' . $zapSkladID;
    }

    public function getQuantityIn(int $zapSkladID)
    {
        return 'quantityIn_' . $zapSkladID;
    }

    public function getQuantityPath(int $zapSkladID)
    {
        return 'quantityPath_' . $zapSkladID;
    }

    public function getReserve(int $zapSkladID)
    {
        return 'reserve_' . $zapSkladID;
    }

    public function getQuantityReturn(int $zapSkladID)
    {
        return 'quantityReturn_' . $zapSkladID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $zapSkladID = $arr[1] ?: 0;
        return $this->incomeSklads[$zapSkladID][$fieldName] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $zapSkladID = $arr[1] ?: 0;
        $this->incomeSklads[$zapSkladID][$fieldName] = $value;
    }
}

<?php

namespace App\Model\Income\UseCase\Income\Label;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var array
     */
    public $incomes;

    public function __construct(array $incomes)
    {
        $this->incomes = $incomes;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $incomeID = $arr[1] ?: 0;
        return $this->incomes[$incomeID][$fieldName] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $incomeID = $arr[1] ?: 0;
        $this->incomes[$incomeID][$fieldName] = $value;
    }
}

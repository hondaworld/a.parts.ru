<?php

namespace App\Model\Order\UseCase\Good\Label;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var array
     */
    public $goods;

    public function __construct(array $goods)
    {
        $this->goods = $goods;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $goodID = $arr[1] ?: 0;
        return $this->goods[$goodID][$fieldName] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $goodID = $arr[1] ?: 0;
        $this->goods[$goodID][$fieldName] = $value;
    }
}

<?php

namespace App\Model\Card\UseCase\StockNumber\Price;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var array
     */
    public $stockNumbers;

    public function __construct(array $stockNumbers)
    {
        $this->stockNumbers = $stockNumbers;
    }

    public function getPrice(int $numberID)
    {
        return 'price_' . $numberID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $price = $arr[0];
        $numberID = $arr[1] ?: 0;
        if (isset($this->stockNumbers[$numberID]))
            return $this->stockNumbers[$numberID];
        else
            return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $price = $arr[0];
        $numberID = $arr[1] ?: 0;
        $this->stockNumbers[$numberID] = $value;
    }
}

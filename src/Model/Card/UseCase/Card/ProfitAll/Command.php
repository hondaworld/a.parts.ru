<?php

namespace App\Model\Card\UseCase\Card\ProfitAll;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $price1;

    public $profit;

    /**
     * @var boolean
     */
    public $is_price_group_fix;

    /**
     * @var int
     */
    public $price_groupID;

    /**
     * @var array
     */
    public $opts;

    /**
     * @var array
     */
    public $profits;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard, array $opts, array $profits): self
    {
        $command = new self($zapCard->getId());
        $command->price1 = $zapCard->getPrice1();
        $command->profit = $zapCard->getProfit();
        $command->is_price_group_fix = $zapCard->isPriceGroupFix();
        $command->price_groupID = $zapCard->getPriceGroup() ? $zapCard->getPriceGroup()->getId() : null;
        $command->opts = $opts;
        $command->profits = $profits;
        return $command;
    }

    public function getProfit(int $optID)
    {
        return 'profit_' . $optID;
    }

    public function getProfitPrice(int $optID)
    {
        return 'profitPrice_' . $optID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $optID = $arr[1] ?: 0;
        if ($profit == 'profit') {
            if (isset($this->profits[$optID]))
                return $this->profits[$optID];
            else
                return null;
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $optID = $arr[1] ?: 0;
        if ($profit == 'profit') {
            $this->profits[$optID] = $value;
        }
    }
}

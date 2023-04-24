<?php

namespace App\Model\Card\UseCase\Card\ProfitZapCard;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapCardID;

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
        $command->opts = $opts;
        $command->profits = $profits;
        return $command;
    }

    public function getProfit(int $optID)
    {
        return 'profit_' . $optID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $optID = $arr[1] ?: 0;
        if (isset($this->profits[$optID]))
            return $this->profits[$optID];
        else
            return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $optID = $arr[1] ?: 0;
        $this->profits[$optID] = $value;
    }
}

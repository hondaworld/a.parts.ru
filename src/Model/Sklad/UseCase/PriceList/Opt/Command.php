<?php

namespace App\Model\Sklad\UseCase\PriceList\Opt;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\Model\Sklad\Entity\PriceList\PriceList;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $price_listID;

    /**
     * @var array
     */
    public $opts;

    /**
     * @var array
     */
    public $profits;

    public function __construct(int $price_listID)
    {
        $this->price_listID = $price_listID;
    }

    public static function fromEntity(PriceList $priceList, array $opts, array $profits): self
    {
        $command = new self($priceList->getId());
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
        if (isset($this->profits[$optID]['profit']))
            return $this->profits[$optID]['profit'];
        else
            return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $optID = $arr[1] ?: 0;
        $this->profits[$optID]['profit'] = $value;
    }
}

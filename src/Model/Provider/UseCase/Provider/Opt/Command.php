<?php

namespace App\Model\Provider\UseCase\Provider\Opt;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerID;

    /**
     * @var array
     */
    public $opts;

    /**
     * @var array
     */
    public $providerPrices;

    /**
     * @var array
     */
    public $profits;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
    }

    public static function fromEntity(Provider $provider, array $opts, array $providerPrices, array $profits): self
    {
        $command = new self($provider->getId());
        $command->opts = $opts;
        $command->providerPrices = $providerPrices;
        $command->profits = $profits;
        return $command;
    }

    public function getProfit(int $providerPriceID, int $optID)
    {
        return 'profit_' . $providerPriceID . '_' . $optID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $providerPriceID = $arr[1] ?: 0;
        $optID = $arr[2] ?: 0;
        if (isset($this->profits[$providerPriceID][$optID]['profit']))
            return $this->profits[$providerPriceID][$optID]['profit'];
        else
            return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $profit = $arr[0];
        $providerPriceID = $arr[1] ?: 0;
        $optID = $arr[2] ?: 0;
        $this->profits[$providerPriceID][$optID]['profit'] = $value;
    }
}

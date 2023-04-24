<?php

namespace App\Model\User\UseCase\User\ShowHidePrices;

use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var array
     */
    public $prices;

    /**
     * @var array
     */
    public $pricesList;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user, array $pricesList): self
    {
        $command = new self($user->getId());
        $command->prices = array_map(function (ProviderPrice $providerPrice): int {
            return $providerPrice->getId();
        }, $user->getShowHidePrices());
        $command->pricesList = $pricesList;
        return $command;
    }
}

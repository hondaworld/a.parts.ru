<?php

namespace App\Model\User\UseCase\User\ShowHidePrices;

use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $prices;

    public function __construct(UserRepository $users, ProviderPriceRepository $prices, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->prices = $prices;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $prices = array_map(function (int $id): ProviderPrice {
            return $this->prices->get($id);
        }, $command->prices);

        $user->updateShowHidePrice($prices);
        $this->flusher->flush();
    }
}

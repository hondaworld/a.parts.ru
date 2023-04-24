<?php

namespace App\Model\Detail\UseCase\Dealer\Edit;

use App\Model\Detail\Entity\Dealer\ShopPriceDealerRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $shopPriceDealerRepository;

    public function __construct(ShopPriceDealerRepository $shopPriceDealerRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->shopPriceDealerRepository = $shopPriceDealerRepository;
    }

    public function handle(Command $command): void
    {
        $shopPriceDealer = $this->shopPriceDealerRepository->get($command->shopPriceDealerID);

        $shopPriceDealer->update(
            $command->price
        );

        $this->flusher->flush();
    }
}

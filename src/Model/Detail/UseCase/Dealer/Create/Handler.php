<?php

namespace App\Model\Detail\UseCase\Dealer\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use App\Model\Detail\Entity\Dealer\ShopPriceDealerRepository;
use App\Model\Flusher;
use App\ReadModel\Detail\ShopPriceDealerFetcher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ShopPriceDealerFetcher $shopPriceDealerFetcher;

    public function __construct(
        ShopPriceDealerRepository $repository,
        CreaterRepository $createrRepository,
        ShopPriceDealerFetcher $shopPriceDealerFetcher,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->shopPriceDealerFetcher = $shopPriceDealerFetcher;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        $creater = $this->createrRepository->get($command->createrID);

        if ($this->shopPriceDealerFetcher->hasDealerPrice($number->getValue(), $command->createrID)) {
            throw new \DomainException('Такая цена уже есть.');
        }

        $shopPriceDealer = new ShopPriceDealer(
            $number,
            $creater,
            $command->price
        );

        $this->repository->add($shopPriceDealer);

        $this->flusher->flush();
    }
}

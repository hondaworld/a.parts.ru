<?php

namespace App\Model\Shop\UseCase\ShopLocation\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Shop\Entity\Location\ShopLocationRepository;

class Handler
{
    private ShopLocationRepository $repository;
    private Flusher $flusher;

    public function __construct(ShopLocationRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shopLocation = new ShopLocation(
            $command->name,
            $command->name_short
        );

        $this->repository->add($shopLocation);

        $this->flusher->flush();
    }
}

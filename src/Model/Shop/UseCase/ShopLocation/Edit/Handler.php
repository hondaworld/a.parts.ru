<?php

namespace App\Model\Shop\UseCase\ShopLocation\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocationRepository;

class Handler
{
    private Flusher $flusher;
    private ShopLocationRepository $repository;

    public function __construct(ShopLocationRepository $repository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $shopLocation = $this->repository->get($command->locationID);

        $shopLocation->update(
            $command->name,
            $command->name_short,
        );

        $this->flusher->flush();
    }
}

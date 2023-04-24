<?php

namespace App\Model\Card\UseCase\Location\Edit;

use App\Model\Card\Entity\Location\ZapSkladLocationRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocationRepository;

class Handler
{
    private $repository;
    private $flusher;
    private ShopLocationRepository $shopLocationRepository;

    public function __construct(ZapSkladLocationRepository $repository, ShopLocationRepository $shopLocationRepository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->shopLocationRepository = $shopLocationRepository;
    }

    public function handle(Command $command): void
    {
        $zapSkladLocation = $this->repository->get($command->zapSkladLocationID);

        $zapSkladLocation->update(
            $command->locationID ? $this->shopLocationRepository->get($command->locationID) : null,
            $command->quantityMin,
            $command->quantityMinIsReal,
            $command->quantityMax
        );

        $this->flusher->flush();
    }
}

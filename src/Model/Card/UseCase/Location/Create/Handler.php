<?php

namespace App\Model\Card\UseCase\Location\Create;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Card\Entity\Location\ZapSkladLocationRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocationRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private $repository;
    private $flusher;
    private ZapCardRepository $zapCardRepository;
    private ZapSkladRepository $zapSkladRepository;
    private ShopLocationRepository $shopLocationRepository;

    public function __construct(ZapSkladLocationRepository $repository, ZapCardRepository $zapCardRepository, ZapSkladRepository $zapSkladRepository,ShopLocationRepository $shopLocationRepository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->shopLocationRepository = $shopLocationRepository;
    }

    public function handle(Command $command): void
    {
        $zapSkladLocation = new ZapSkladLocation(
            $this->zapCardRepository->get($command->zapCardID),
            $this->zapSkladRepository->get($command->zapSkladID),
            $command->locationID ? $this->shopLocationRepository->get($command->locationID) : null,
            $command->quantityMin,
            $command->quantityMinIsReal,
            $command->quantityMax
        );

        $this->repository->add($zapSkladLocation);

        $this->flusher->flush();
    }
}

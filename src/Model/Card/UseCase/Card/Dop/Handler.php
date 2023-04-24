<?php

namespace App\Model\Card\UseCase\Card\Dop;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;

class Handler
{
    private $repository;
    private $flusher;
    private CountryRepository $countryRepository;
    private ShopTypeRepository $shopTypeRepository;
    private EdIzmRepository $edIzmRepository;

    public function __construct(
        ZapCardRepository $repository,
        CountryRepository $countryRepository,
        ShopTypeRepository $shopTypeRepository,
        EdIzmRepository $edIzmRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->countryRepository = $countryRepository;
        $this->shopTypeRepository = $shopTypeRepository;
        $this->edIzmRepository = $edIzmRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateDop(
            $command->countryID ? $this->countryRepository->get($command->countryID) : null,
            $this->shopTypeRepository->get($command->shop_typeID),
            $this->edIzmRepository->get($command->ed_izmID)
        );

        $this->flusher->flush();
    }
}

<?php

namespace App\Model\Card\UseCase\Card\Country;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private CountryRepository $countryRepository;

    public function __construct(
        ZapCardRepository $repository,
        CountryRepository $countryRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->countryRepository = $countryRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateCountry(
            $command->countryID ? $this->countryRepository->get($command->countryID) : null
        );

        $this->flusher->flush();
    }
}

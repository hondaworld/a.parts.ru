<?php

namespace App\Model\Card\UseCase\Card\Price;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;

class Handler
{
    private $repository;
    private $providerPriceRepository;
    private $flusher;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        ZapCardRepository $repository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $providerPrice = null;
        $currency = null;
        if ($command->currency_providerPriceID) {
            $providerPrice = $this->providerPriceRepository->get($command->currency_providerPriceID);
            $currency = $providerPrice->getCurrency();
        }

        $zapCard->updatePrice(
            $command->price,
            $command->currency_price,
            $providerPrice,
            $currency
        );

        $this->flusher->flush();
    }
}

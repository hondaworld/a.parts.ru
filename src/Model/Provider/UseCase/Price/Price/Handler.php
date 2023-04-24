<?php

namespace App\Model\Provider\UseCase\Price\Price;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\Price;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;

class Handler
{
    private ProviderPriceRepository $providerPriceRepository;
    private Flusher $flusher;
    private CreaterRepository $createrRepository;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        CreaterRepository $createrRepository,
        Flusher $flusher
    )
    {
        $this->providerPriceRepository = $providerPriceRepository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {
        $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);

        $price = new Price(
            $command->razd,
            $command->razd_decimal,
            $command->price,
            $command->price_copy,
            $command->price_email,
            $command->email_from,
            $command->isNotCheckExt,
            $command->isUpdate,
            $command->rg_value,
            $command->priceadd
        );

        $providerPrice->updatePrice(
            $price,
            $command->superProviderPriceID ? $this->providerPriceRepository->get($command->superProviderPriceID) : null,
            $command->createrID ? $this->createrRepository->get($command->createrID) : null
        );

        $this->flusher->flush();
    }
}

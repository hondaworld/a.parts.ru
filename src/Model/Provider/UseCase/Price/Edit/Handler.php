<?php

namespace App\Model\Provider\UseCase\Price\Edit;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Group\ProviderPriceGroupRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private $providerPriceRepository;
    private $providerRepository;
    private $flusher;
    private $currencyRepository;
    private $providerPriceGroupRepository;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        ProviderRepository $providerRepository,
        CurrencyRepository $currencyRepository,
        ProviderPriceGroupRepository $providerPriceGroupRepository,
        Flusher $flusher
    )
    {
        $this->providerPriceRepository = $providerPriceRepository;
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->currencyRepository = $currencyRepository;
        $this->providerPriceGroupRepository = $providerPriceGroupRepository;
    }

    public function handle(Command $command): void
    {
        $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);

        $providerPrice->update(
            $this->providerPriceGroupRepository->get($command->providerPriceGroupID),
            $this->providerRepository->get($command->providerID),
            $command->name,
            $command->description,
            $command->srok,
            $command->srokInDays,
            $this->currencyRepository->get($command->currencyID),
            $command->koef,
            $command->currencyOwn,
            $command->deliveryForWeight,
            $command->deliveryInPercent,
            $command->discount,
            $command->daysofchanged,
            $command->clients_hide
        );

        $this->flusher->flush();
    }
}

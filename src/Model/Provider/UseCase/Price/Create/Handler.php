<?php

namespace App\Model\Provider\UseCase\Price\Create;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Group\ProviderPriceGroupRepository;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private ProviderRepository $providerRepository;
    private Flusher $flusher;
    private CurrencyRepository $currencyRepository;
    private ProviderPriceGroupRepository $providerPriceGroupRepository;

    public function __construct(
        ProviderRepository $providerRepository,
        CurrencyRepository $currencyRepository,
        ProviderPriceGroupRepository $providerPriceGroupRepository,
        Flusher $flusher
    )
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->currencyRepository = $currencyRepository;
        $this->providerPriceGroupRepository = $providerPriceGroupRepository;
    }

    public function handle(Command $command): ProviderPrice
    {
        $provider = $this->providerRepository->get($command->providerID);
        $providerPrice = new ProviderPrice(
            $this->providerPriceGroupRepository->get($command->providerPriceGroupID),
            $provider,
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

        $provider->assignPrice($providerPrice);

        $this->flusher->flush();

        return $providerPrice;
    }
}

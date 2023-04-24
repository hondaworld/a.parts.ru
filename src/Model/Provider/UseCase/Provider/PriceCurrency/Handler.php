<?php

namespace App\Model\Provider\UseCase\Provider\PriceCurrency;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private ProviderRepository $providerRepository;
    private CurrencyRepository $currencyRepository;
    private Flusher $flusher;

    public function __construct(ProviderRepository $providerRepository, CurrencyRepository $currencyRepository, Flusher $flusher)
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->currencyRepository = $currencyRepository;
    }

    public function handle(Command $command): void
    {
        $provider = $this->providerRepository->get($command->providerID);
        $currency = $this->currencyRepository->get($command->currencyID);

        $provider->updateCurrencyForAllProviderPrices($currency, $command->koef, $command->currencyOwn);

        $this->flusher->flush();
    }
}

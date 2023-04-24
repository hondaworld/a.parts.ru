<?php

namespace App\Model\Provider\UseCase\Provider\Opt;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\User\OptFetcher;

class Handler
{
    private ProviderRepository $providerRepository;
    private Flusher $flusher;
    private OptFetcher $optFetcher;
    private ProviderPriceFetcher $providerPriceFetcher;

    public function __construct(ProviderRepository $providerRepository, OptFetcher $optFetcher, ProviderPriceFetcher $providerPriceFetcher, Flusher $flusher)
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->optFetcher = $optFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
    }

    public function handle(Command $command): void
    {
        $provider = $this->providerRepository->get($command->providerID);
        $opts = $this->optFetcher->assoc();
        $providerPrices = $this->providerPriceFetcher->assocByProvider($provider);

        $provider->clearPriceProfits();
        $this->flusher->flush();

        foreach ($opts as $optID => $optName) {
            $opt = $this->optFetcher->get($optID);

            foreach ($providerPrices as $providerPriceID => $providerPrice) {
                $profit = $command->{'profit_' . $providerPriceID . '_' . $optID};
                if ($profit && floatval($profit) > 0) {
                    $providerPrice = $this->providerPriceFetcher->get($providerPriceID);
                    $providerPrice->assignProfit($opt, $profit);
                }
            }
        }

        $this->flusher->flush();
    }
}

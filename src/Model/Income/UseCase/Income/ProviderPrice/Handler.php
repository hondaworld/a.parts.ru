<?php

namespace App\Model\Income\UseCase\Income\ProviderPrice;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private $flusher;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        PartPriceService $partPriceService,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
        $this->providerPriceRepository = $providerPriceRepository;
    }

    public function handle(Command $command, Income $income): void
    {
        $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);

        $prices = $this->partPriceService->onePrice($income->getZapCard()->getNumber(), $income->getZapCard()->getCreater(), $providerPrice);

        $income->updateProviderPrice($providerPrice);
        $income->updatePrices($prices['priceZak'], $prices['priceDostUsd'], $prices['priceWithDostRub']);

        $this->flusher->flush();
    }
}

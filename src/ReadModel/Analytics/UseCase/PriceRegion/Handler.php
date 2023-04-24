<?php

namespace App\ReadModel\Analytics\UseCase\PriceRegion;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private Flusher $flusher;
    private PartPriceService $partPriceService;
    private PriceGroupRepository $priceGroupRepository;

    public function __construct(
        PartPriceService $partPriceService,
        PriceGroupRepository $priceGroupRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
        $this->priceGroupRepository = $priceGroupRepository;
    }

    public function handle(ProviderPrice $providerPrice, ZapCard $zapCard): array
    {
        $prices = $this->partPriceService->onePrice($zapCard->getNumber(), $zapCard->getCreater(), $providerPrice);

        $zapCard->updatePrice($prices['priceWithDostRub'], $prices['priceZak'] + $prices['priceDostUsd'], $providerPrice, $providerPrice->getCurrency());
        if (!$zapCard->isPriceGroupFix()) {
            $zapCard->updatePriceGroup($this->priceGroupRepository->getForIncomeInWarehouse($providerPrice), $zapCard->isPriceGroupFix());
        }

        $this->flusher->flush();
        return [
            'providerPrice' => $providerPrice->getDescription(),
            'price' => $prices['priceWithDostRub']
        ];
    }
}

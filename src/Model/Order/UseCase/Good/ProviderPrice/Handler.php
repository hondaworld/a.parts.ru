<?php

namespace App\Model\Order\UseCase\Good\ProviderPrice;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private Flusher $flusher;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;
    private ZapSkladRepository $zapSkladRepository;
    private ZapCardPriceService $zapCardPriceService;
    private ZapCardRepository $zapCardRepository;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        PartPriceService        $partPriceService,
        ZapSkladRepository      $zapSkladRepository,
        ZapCardPriceService     $zapCardPriceService,
        ZapCardRepository       $zapCardRepository,
        Flusher                 $flusher
    )
    {
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->zapCardRepository = $zapCardRepository;
    }

    public function handle(Command $command, OrderGood $orderGood, Manager $manager): void
    {
        if ($command->providerPriceID) {
            $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);
            $orderGood->updateProviderPrice($providerPrice);
            if ($command->isPrice == 1) {
                $price = $this->partPriceService->onePriceClient($orderGood->getNumber(), $orderGood->getCreater(), $providerPrice, $orderGood->getOrder()->getUser()->getOpt());
//                $stock = $this->partPriceService->getPriceStock($orderGood->getNumber(), $orderGood->getCreater(), $providerPrice);
//                if ($stock && $stock['price_stock'] > 0 && $stock['price_stock'] < $price) $price = $stock['price_stock'];
                $orderGood->updatePrice($price, $orderGood->getDiscount());
            }

            $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Изменение региона на " . $providerPrice->getDescription(), $orderGood->getNumber()->getValue());
        }

        if ($command->zapSkladID) {
            $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);
            $orderGood->updateZapSklad($zapSklad);
            if ($command->isPrice == 1) {
                $zapCard = $this->zapCardRepository->getByNumberAndCreater($orderGood->getNumber(), $orderGood->getCreater());
                $price = $this->zapCardPriceService->priceOpt($zapCard, $orderGood->getOrder()->getUser()->getOpt());
//                $stock = $this->partPriceService->getPriceStock($orderGood->getNumber(), $orderGood->getCreater());
//                if ($stock && $stock['price_stock'] > 0 && $stock['price_stock'] < $price) $price = $stock['price_stock'];
                $orderGood->updatePrice($price, $orderGood->getDiscount());
            }

            $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Изменение склада на " . $zapSklad->getNameShort(), $orderGood->getNumber()->getValue());
        }

        $this->flusher->flush();
    }
}

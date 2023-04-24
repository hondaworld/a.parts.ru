<?php

namespace App\Model\Sklad\UseCase\Parts\Location;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Shop\Entity\Location\ShopLocationRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;

class Handler
{
    private Flusher $flusher;
    private ShopLocationRepository $shopLocationRepository;

    public function __construct(
        ShopLocationRepository     $shopLocationRepository,
        Flusher                    $flusher
    )
    {
        $this->flusher = $flusher;
        $this->shopLocationRepository = $shopLocationRepository;
    }

    public function handle(Command $command, ZapCard $zapCard, ZapSklad $zapSklad): void
    {
        if (!$command->location) {
            $zapSkladLocation = $zapCard->getLocationByZapSklad($zapSklad);
            if ($zapSkladLocation) {
                $zapSkladLocation->updateShopLocation(null);
            }
        } else {
            $location = $this->shopLocationRepository->getByName($command->location);
            if (!$location) {
                if (!$command->isCreate) {
                    throw new \DomainException('Ячейка не существует');
                } else {
                    $location = new ShopLocation(strtoupper($command->location), strtoupper($command->location));
                    $this->shopLocationRepository->add($location);
                }
            }
            $zapSkladLocation = $zapCard->getLocationByZapSklad($zapSklad);
            if ($zapSkladLocation) {
                $zapSkladLocation->updateShopLocation($location);
            } else {
                $zapCard->assignLocation($zapSklad, $location);
            }
        }
        $this->flusher->flush();
    }
}

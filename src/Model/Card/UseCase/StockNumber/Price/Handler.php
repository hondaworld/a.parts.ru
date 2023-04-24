<?php

namespace App\Model\Card\UseCase\StockNumber\Price;

use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $zapCardStockNumberRepository;

    public function __construct(ZapCardStockNumberRepository $zapCardStockNumberRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->zapCardStockNumberRepository = $zapCardStockNumberRepository;
    }

    public function handle(Command $command): void
    {
        foreach ($command->stockNumbers as $numberID => $price_stock) {
            $stockNumber = $this->zapCardStockNumberRepository->get($numberID);
            $stockNumber->updatePriceStock($price_stock);
        }

        $this->flusher->flush();
    }
}

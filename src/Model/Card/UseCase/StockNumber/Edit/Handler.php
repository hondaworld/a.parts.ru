<?php

namespace App\Model\Card\UseCase\StockNumber\Edit;

use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Flusher;

class Handler
{
    private $zapCardStockRepository;
    private $flusher;
    private $zapCardStockNumberRepository;

    public function __construct(ZapCardStockRepository $zapCardStockRepository, ZapCardStockNumberRepository $zapCardStockNumberRepository, Flusher $flusher)
    {
        $this->zapCardStockRepository = $zapCardStockRepository;
        $this->flusher = $flusher;
        $this->zapCardStockNumberRepository = $zapCardStockNumberRepository;
    }

    public function handle(Command $command): void
    {
        $stockNumber = $this->zapCardStockNumberRepository->get($command->numberID);

        $stockNumber->update(
            $this->zapCardStockRepository->get($command->stockID),
            $command->price_stock
        );

        $this->flusher->flush();
    }
}

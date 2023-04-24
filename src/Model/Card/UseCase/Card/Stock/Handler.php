<?php

namespace App\Model\Card\UseCase\Card\Stock;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Flusher;

class Handler
{
    private $zapCardStockRepository;
    private $flusher;
    private $zapCardStockNumberRepository;
    private $zapCardRepository;

    public function __construct(ZapCardRepository $zapCardRepository, ZapCardStockRepository $zapCardStockRepository, ZapCardStockNumberRepository $zapCardStockNumberRepository, Flusher $flusher)
    {
        $this->zapCardRepository = $zapCardRepository;
        $this->zapCardStockRepository = $zapCardStockRepository;
        $this->flusher = $flusher;
        $this->zapCardStockNumberRepository = $zapCardStockNumberRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->zapCardRepository->get($command->zapCardID);
        if ($command->stockID) {
            if (!$command->numberID) {
                $stockNumber = new ZapCardStockNumber(
                    $this->zapCardStockRepository->get($command->stockID),
                    $zapCard->getNumber(),
                    $zapCard->getCreater(),
                    $command->price_stock
                );
                $this->zapCardStockNumberRepository->add($stockNumber);
            } else {
                $stockNumber = $this->zapCardStockNumberRepository->get($command->numberID);

                $stockNumber->update(
                    $this->zapCardStockRepository->get($command->stockID),
                    $command->price_stock
                );
            }
            $this->flusher->flush();
        } else {
            $this->zapCardStockNumberRepository->deleteWithNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater());
        }
    }
}

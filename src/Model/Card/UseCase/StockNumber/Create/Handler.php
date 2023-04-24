<?php

namespace App\Model\Card\UseCase\StockNumber\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;

class Handler
{
    private $zapCardStockNumberRepository;
    private $flusher;
    private $createrRepository;

    public function __construct(ZapCardStockNumberRepository $zapCardStockNumberRepository, CreaterRepository $createrRepository, Flusher $flusher)
    {
        $this->zapCardStockNumberRepository = $zapCardStockNumberRepository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {
        $arrNumbers = explode("\n", $command->numbers);
        foreach ($arrNumbers as $arrNumber) {
            $number = new DetailNumber($arrNumber);
            if ($number->getValue() != '') {
                $creater = $this->createrRepository->get($command->createrID);

                $this->zapCardStockNumberRepository->deleteWithNumberAndCreater($number, $creater);

                $zapCardStock = new ZapCardStockNumber(
                    $command->stock,
                    $number,
                    $creater,
                    $command->price_stock
                );
                $this->zapCardStockNumberRepository->add($zapCardStock);
            }
        }

        $this->flusher->flush();
    }
}

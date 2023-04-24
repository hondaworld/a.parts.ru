<?php

namespace App\Model\Card\UseCase\StockNumber\Upload;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function handle(Command $command, UploadedFile $file): void
    {
        $creater = $this->createrRepository->get($command->createrID);

        $DataFile = fopen($file->getPathname(), "r");
        while (!feof($DataFile)) {
            $line = fgetcsv($DataFile, 4096, ';', '"', '"');

            if ($line) {
                $number = new DetailNumber($line[0]);
                $price_stock = trim($line[1]);

                if ($number->getValue() != '' && $price_stock != '') {
                    $this->zapCardStockNumberRepository->deleteWithNumberAndCreater($number, $creater);

                    $zapCardStock = new ZapCardStockNumber(
                        $command->stock,
                        $number,
                        $creater,
                        $price_stock
                    );
                    $this->zapCardStockNumberRepository->add($zapCardStock);
                }
            }
        }


        $this->flusher->flush();
    }
}

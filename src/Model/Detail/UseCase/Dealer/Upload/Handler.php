<?php

namespace App\Model\Detail\UseCase\Dealer\Upload;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use App\Model\Detail\Entity\Dealer\ShopPriceDealerRepository;
use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;
use App\ReadModel\Detail\ShopPriceDealerFetcher;
use App\Service\CsvUploadHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private CsvUploadHelper $csvUploadHelper;
    private ShopPriceDealerFetcher $fetcher;

    public function __construct(
        ShopPriceDealerRepository $repository,
        ShopPriceDealerFetcher $fetcher,
        CreaterRepository $createrRepository,
        Flusher $flusher,
        CsvUploadHelper $csvUploadHelper
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->csvUploadHelper = $csvUploadHelper;
        $this->fetcher = $fetcher;
    }

    public function handle(Command $command, UploadedFile $file): array
    {
        $data = [
            'done' => 0,
            'update' => 0
        ];

        $creater = $this->createrRepository->get($command->createrID);

        if ($command->isDelete) {
            $this->fetcher->deleteByCreaterAndNumber($command->createrID);
        }

        $koef = $command->koef ? $command->koef : 0;
        $koef = floatval(str_replace(',', '.', $koef));

        $DataFile = fopen($file->getPathname(), "r");
        while (!feof($DataFile)) {
            $line = $this->csvUploadHelper->getCsvLine($DataFile);

            if ($line) {
                $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[$command->numNumber])));
                $price = trim($line[$command->numPrice]);
                $price = str_replace(',', '.', $price);

                if ($number->getValue() != '' && floatval($price) > 0) {

                    if ($koef > 0) $price = $price * $koef;

                    if ($command->isDelete) {
                        $shopPriceDealer = null;
                    } else {
                        $shopPriceDealer = $this->repository->findByNumberAndCreater($number, $creater);
                    }

                    if ($shopPriceDealer) {
                        $shopPriceDealer->update($price);
                        $data['update']++;
                    } else {
                        $shopPriceDealer = new ShopPriceDealer($number, $creater, $price);
                        $this->repository->add($shopPriceDealer);
                        $data['done']++;
                    }
                }
            }
        }

        $this->flusher->flush();

        return $data;
    }
}

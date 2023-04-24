<?php

namespace App\ReadModel\Analytics\UseCase\ComparePrice;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Detail\Entity\Zamena\ShopZamenaRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Detail\ShopZamenaFetcher;
use App\Service\CsvUploadHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ShopZamenaFetcher $shopZamenaFetcher;
    private CsvUploadHelper $csvUploadHelper;

    public function __construct(
        ShopZamenaRepository $repository,
        CreaterRepository $createrRepository,
        ShopZamenaFetcher $shopZamenaFetcher,
        Flusher $flusher,
        CsvUploadHelper $csvUploadHelper
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->shopZamenaFetcher = $shopZamenaFetcher;
        $this->csvUploadHelper = $csvUploadHelper;
    }

    public function handle(Command $command, UploadedFile $file, Manager $manager): array
    {
        $data = [
            'done' => 0,
            'exist' => 0
        ];

        $creater = $this->createrRepository->get($command->createrID);
        $creater2 = $this->createrRepository->get($command->createrID2);

        $DataFile = fopen($file->getPathname(), "r");
        while (!feof($DataFile)) {
            $line = $this->csvUploadHelper->getCsvLine($DataFile);

            if ($line) {
                $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[0])));
                $number2 = new DetailNumber($this->csvUploadHelper->convertText(trim($line[1])));

                if ($number->getValue() != '' && $number2->getValue() != '') {

                    if (!$this->shopZamenaFetcher->hasZamena(
                            $number->getValue(),
                            $command->createrID,
                            $number2->getValue(),
                            $command->createrID2
                        ) &&
                        !($number->isEqual($number2) && $command->createrID == $command->createrID2)
                    ) {
                        $shopZamena = new ShopZamena($number, $creater, $number2, $creater2, $manager);
                        $this->repository->add($shopZamena);
                        $data['done']++;
                    } else {
                        $data['exist']++;
                    }

                }
            }
        }

        $this->flusher->flush();

        return $data;
    }
}

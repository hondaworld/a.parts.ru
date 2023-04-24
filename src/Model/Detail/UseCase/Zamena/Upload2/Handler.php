<?php

namespace App\Model\Detail\UseCase\Zamena\Upload2;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Detail\Entity\Zamena\ShopZamenaRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Detail\ShopZamenaFetcher;
use App\Service\CsvUploadHelper;
use App\Service\Detail\CreaterService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ShopZamenaFetcher $shopZamenaFetcher;
    private CsvUploadHelper $csvUploadHelper;
    private CreaterService $createrService;

    public function __construct(
        ShopZamenaRepository $repository,
        CreaterRepository $createrRepository,
        ShopZamenaFetcher $shopZamenaFetcher,
        Flusher $flusher,
        CsvUploadHelper $csvUploadHelper,
        CreaterService $createrService
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->shopZamenaFetcher = $shopZamenaFetcher;
        $this->csvUploadHelper = $csvUploadHelper;
        $this->createrService = $createrService;
    }

    public function handle(Command $command, UploadedFile $file, Manager $manager): array
    {
        $data = [
            'done' => 0,
            'exist' => 0,
            'notFound' => 0
        ];

        $DataFile = fopen($file->getPathname(), "r");
        while (!feof($DataFile)) {
            $line = $this->csvUploadHelper->getCsvLine($DataFile);

            if ($line) {
                $createrName = trim($line[0]);
                $creater2Name = trim($line[2]);

                $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[1])));
                $number2 = new DetailNumber($this->csvUploadHelper->convertText(trim($line[3])));

                $createrID = $this->createrService->findCreaterIDFromCsv($createrName);
                $createrID2 = $this->createrService->findCreaterIDFromCsv($creater2Name);

                if ($number->getValue() != '' && $number2->getValue() != '' && $createrID != null && $createrID2 != null) {

                    if (!$this->shopZamenaFetcher->hasZamena(
                            $number->getValue(),
                            $createrID,
                            $number2->getValue(),
                            $createrID2
                        ) &&
                        !($number->isEqual($number2) && $createrID == $createrID2)
                    ) {
                        $shopZamena = new ShopZamena($number, $this->createrRepository->get($createrID), $number2, $this->createrRepository->get($createrID2), $manager);
                        $this->repository->add($shopZamena);
                        $data['done']++;
                    } else {
                            $data['exist']++;
                    }

                } else {
                    if ($createrID == null || $createrID2 == null)
                        $data['notFound']++;
                }
            }
        }

        $this->flusher->flush();

        return $data;
    }
}

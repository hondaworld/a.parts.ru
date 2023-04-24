<?php

namespace App\Model\Detail\UseCase\Weight\Upload2;

use App\Model\Card\Entity\Card\DetailNumber;
use App\ReadModel\Detail\WeightFetcher;
use App\Service\CsvUploadHelper;
use App\Service\Detail\CreaterService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Handler
{
    private CsvUploadHelper $csvUploadHelper;
    private CreaterService $createrService;
    private WeightFetcher $weightFetcher;

    public function __construct(
        WeightFetcher     $weightFetcher,
        CsvUploadHelper   $csvUploadHelper,
        CreaterService    $createrService
    )
    {
        $this->csvUploadHelper = $csvUploadHelper;
        $this->createrService = $createrService;
        $this->weightFetcher = $weightFetcher;
    }

    public function handle(Command $command, UploadedFile $file): array
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
                $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[1])));
                $val = trim($line[2]);
                $val = str_replace(',', '.', $val);

                $createrID = $this->createrService->findCreaterIDFromCsv($createrName);

                if ($number->getValue() != '' && $createrID != null && floatval($val) > 0) {

                    //$creater = $this->createrRepository->get($createrID);

                    //$weight = $this->repository->findByNumberAndCreater($number, $creater);

                    $weight = $this->weightFetcher->oneByNumberAndCreater($number->getValue(), $createrID);

                    if ($weight) {
                        if ($weight['weightIsReal'] == 0) {
                            $this->weightFetcher->updateWeight($val, $weight['weightID']);
                            $data['done']++;
                        } else {
                            $data['exist']++;
                        }
                    } else {
                        $this->weightFetcher->insertWeight([
                            'number' => $number->getValue(),
                            'createrID' => $createrID,
                            'weight' => floatval($val),
                            'weightIsReal' => 0,
                        ]);
                        $data['done']++;
                    }

//                    if ($weight) {
//                        if (!$weight->getWeightIsReal()) {
//                            $weight->updateWeight($val);
//                            $data['done']++;
//                        } else {
//                            $data['exist']++;
//                        }
//                    } else {
//                        $weight = new Weight($number, $creater, $val, false);
//                        $this->repository->add($weight);
//                        $data['done']++;
//                    }
//                    $this->flusher->flush();

                } else {
                    if ($createrID == null)
                        $data['notFound']++;
                }
            }
        }


        return $data;
    }
}

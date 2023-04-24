<?php

namespace App\Model\Detail\UseCase\Weight\Upload1;

use App\Model\Card\Entity\Card\DetailNumber;
use App\ReadModel\Detail\WeightFetcher;
use App\Service\CsvUploadHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Handler
{
    private CsvUploadHelper $csvUploadHelper;
    private WeightFetcher $weightFetcher;

    public function __construct(
        WeightFetcher     $weightFetcher,
        CsvUploadHelper   $csvUploadHelper
    )
    {
        $this->csvUploadHelper = $csvUploadHelper;
        $this->weightFetcher = $weightFetcher;
    }

    public function handle(Command $command, UploadedFile $file): array
    {
        $data = [
            'done' => 0,
            'exist' => 0
        ];

//        $creater = $this->createrRepository->get($command->createrID);

        $DataFile = fopen($file->getPathname(), "r");
        while (!feof($DataFile)) {
            $line = $this->csvUploadHelper->getCsvLine($DataFile);

            if ($line) {
                $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[0])));
                $val = trim($line[1]);
                $val = str_replace(',', '.', $val);

                if ($number->getValue() != '' && floatval($val) > 0) {

//                    $weight = $this->repository->findByNumberAndCreater($number, $creater);
                    $weight = $this->weightFetcher->oneByNumberAndCreater($number->getValue(), $command->createrID);
//
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
                            'createrID' => $command->createrID,
                            'weight' => floatval($val),
                            'weightIsReal' => 0,
                        ]);
//                        $weight = new Weight($number, $creater, $val, false);
//                        $this->repository->add($weight);
//                        $this->flusher->flush();
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
                }
            }
        }


        return $data;
    }
}

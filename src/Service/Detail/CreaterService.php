<?php


namespace App\Service\Detail;


use App\ReadModel\Detail\CreaterFetcher;
use App\Service\CsvUploadHelper;

class CreaterService
{
    private array $creaters;
    private CsvUploadHelper $csvUploadHelper;

    public function __construct(CreaterFetcher $fetcher, CsvUploadHelper $csvUploadHelper)
    {
        $this->creaters = $fetcher->allArray();
        $this->csvUploadHelper = $csvUploadHelper;
    }

    public function findCreaterIDFromCsv(string $createrName): ?int
    {
        $createrName = $this->csvUploadHelper->convertText(trim($createrName));

        foreach ($this->creaters as $createrID => $creater) {
            if (mb_strtolower($creater['name']) == mb_strtolower($createrName)) return $createrID;
        }

        return null;
    }

    public function findCreaterFromCsv(string $createrName): ?array
    {
        $createrName = $this->csvUploadHelper->convertText(trim($createrName));

        foreach ($this->creaters as $createrID => $creater) {
            if (mb_strtolower($creater['name']) == mb_strtolower($createrName)) return $creater + ['createrID' => $createrID];
        }

        return null;
    }
}
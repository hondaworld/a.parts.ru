<?php

namespace App\Service\Api;

use App\Model\Card\Entity\Card\DetailNumber;
use App\ReadModel\Detail\PartPriceFetcher;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\Service\Detail\CreaterService;

class ApiJap
{
    private $apiToken = 'FdfbfhhoGLD59WkpHFE7aj30qWN4nCFSFOX-7dy3CmvoLX6fs_';
    private $url = 'https://yumbo-jp.com/api/v1/parts/search.json?partNo=';
    private $providerPriceID = 311;
    private CreaterService $createrService;
    private PartPriceFetcher $partPriceFetcher;
    private PriceUploaderFetcher $priceUploaderFetcher;

    public function __construct(
        CreaterService $createrService,
        PartPriceFetcher $partPriceFetcher,
        PriceUploaderFetcher $priceUploaderFetcher
    )
    {
        $this->createrService = $createrService;
        $this->partPriceFetcher = $partPriceFetcher;
        $this->priceUploaderFetcher = $priceUploaderFetcher;
    }

    public function get(DetailNumber $number): bool
    {
        if ($this->partPriceFetcher->existNeorig($number)) return false;
        if ($this->partPriceFetcher->existOriginal($number, $this->providerPriceID)) return false;

        $arr = $this->scan($number);
        if ($arr) {
            foreach ($arr as $item) {
                $createrName = $item['markName'];
                $creater = $this->createrService->findCreaterFromCsv($createrName);
                if ($creater != null && isset($item['priceRub']) && $item['priceRub'] > 0) {
                    $this->priceUploaderFetcher->deletePrice($creater['tableName'], $this->providerPriceID, $creater['createrID'], $number->getValue());
                    $this->priceUploaderFetcher->insertPrice($creater['tableName'], [
                        'number' => $number->getValue(),
                        'createrID' => $creater['createrID'],
                        'providerPriceID' => $this->providerPriceID,
                        'dateOfChanged' => (new \DateTime())->format('Y-m-d H:i:s'),
                        'price' => $item['priceRub'],
                    ]);
                    return true;
                }
            }
        }
        return false;
    }

    private function scan(DetailNumber $number): array
    {
        $headers = [
            'X-Api-Key: ' . $this->apiToken,
            'accept: application/json'
        ];
// Send request to Server
        $ch = curl_init($this->url . $number->getValue());
// To save response in a variable from server, set headers;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// Get response
        $response = curl_exec($ch);
// Decode
        $result = json_decode($response, true);

        curl_close($ch);

        return $result ?: [];
    }
}
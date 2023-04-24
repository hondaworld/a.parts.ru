<?php

namespace App\Model\Sklad\UseCase\PriceList\Opt;

use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceList\PriceListRepository;
use App\ReadModel\User\OptFetcher;

class Handler
{
    private PriceListRepository $priceListRepository;
    private Flusher $flusher;
    private OptFetcher $optFetcher;

    public function __construct(PriceListRepository $priceListRepository, OptFetcher $optFetcher, Flusher $flusher)
    {
        $this->priceListRepository = $priceListRepository;
        $this->flusher = $flusher;
        $this->optFetcher = $optFetcher;
    }

    public function handle(Command $command): void
    {
        $priceList = $this->priceListRepository->get($command->price_listID);
        $opts = $this->optFetcher->assoc();

        $priceList->clearProfits();
        $this->flusher->flush();

        foreach ($opts as $optID => $optName) {
            $opt = $this->optFetcher->get($optID);
            $profit = $command->{'profit_' . $optID};
            $profit = str_replace(',', '.', $profit);
            if ($profit && floatval($profit) > 0) {
                $priceList->assignProfit($opt, $profit);
            }
        }

        $this->flusher->flush();
    }
}

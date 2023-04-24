<?php

namespace App\Model\Sklad\UseCase\PriceList\Create;

use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\Sklad\Entity\PriceList\PriceListRepository;

class Handler
{
    private PriceListRepository $priceListRepository;
    private Flusher $flusher;

    public function __construct(PriceListRepository $priceListRepository, Flusher $flusher)
    {
        $this->priceListRepository = $priceListRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): PriceList
    {
        if ($command->isMain) {
            $this->priceListRepository->updateMain();
        }

        $priceList = new PriceList(
            $command->name,
            $command->koef_dealer,
            $command->no_discount,
            $command->isMain
        );

        $this->priceListRepository->add($priceList);

        $this->flusher->flush();

        return $priceList;
    }
}

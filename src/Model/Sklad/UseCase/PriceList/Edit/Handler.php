<?php

namespace App\Model\Sklad\UseCase\PriceList\Edit;

use App\Model\Flusher;
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

    public function handle(Command $command): void
    {
        $priceList = $this->priceListRepository->get($command->price_listID);

        if ($command->isMain && !$priceList->isMain()) {
            $this->priceListRepository->updateMain();
        }

        $priceList->update(
            $command->name,
            $command->koef_dealer,
            $command->no_discount,
            $command->isMain
        );

        $this->flusher->flush();
    }
}

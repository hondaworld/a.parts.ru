<?php

namespace App\Model\Sklad\UseCase\PriceGroup\Edit;

use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;

class Handler
{
    private PriceGroupRepository $priceGroupRepository;
    private Flusher $flusher;

    public function __construct(PriceGroupRepository $priceGroupRepository, Flusher $flusher)
    {
        $this->priceGroupRepository = $priceGroupRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $priceGroup = $this->priceGroupRepository->get($command->price_groupID);

        if ($command->isMain && !$priceGroup->isMain()) {
            $this->priceGroupRepository->updateMain($priceGroup->getPriceList());
        }

        $priceGroup->update(
            $command->name,
            $command->isMain
        );

        $this->flusher->flush();
    }
}

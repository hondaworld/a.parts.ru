<?php

namespace App\Model\Sklad\UseCase\PriceGroup\Create;

use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
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
        if ($command->isMain) {
            $this->priceGroupRepository->updateMain($command->priceList);
        }

        $priceGroup = new PriceGroup(
            $command->priceList,
            $command->name,
            $command->isMain
        );

        $this->priceGroupRepository->add($priceGroup);

        $this->flusher->flush();
    }
}

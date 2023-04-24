<?php

namespace App\Model\Card\UseCase\Card\ProfitPriceGroup;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;

class Handler
{
    private $zapCardRepository;
    private $flusher;
    private $priceGroupRepository;

    public function __construct(ZapCardRepository $zapCardRepository, PriceGroupRepository  $priceGroupRepository, Flusher $flusher)
    {
        $this->zapCardRepository = $zapCardRepository;
        $this->flusher = $flusher;
        $this->priceGroupRepository = $priceGroupRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->zapCardRepository->get($command->zapCardID);

        $zapCard->updatePriceGroup(
            $command->price_groupID ? $this->priceGroupRepository->get($command->price_groupID) : null,
            $command->is_price_group_fix
        );

        $this->flusher->flush();
    }
}

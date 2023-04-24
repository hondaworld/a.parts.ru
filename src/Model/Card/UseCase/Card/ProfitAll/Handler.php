<?php

namespace App\Model\Card\UseCase\Card\ProfitAll;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;

class Handler
{
    private ZapCardRepository $zapCardRepository;
    private Flusher $flusher;
    private PriceGroupRepository $priceGroupRepository;

    public function __construct(ZapCardRepository $zapCardRepository, PriceGroupRepository  $priceGroupRepository, Flusher $flusher)
    {
        $this->zapCardRepository = $zapCardRepository;
        $this->flusher = $flusher;
        $this->priceGroupRepository = $priceGroupRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->zapCardRepository->get($command->zapCardID);
        $opts = $command->opts;

        $zapCard->updateProfit(
            $command->price1,
            $command->profit
        );

        $zapCard->updatePriceGroup(
            $command->price_groupID ? $this->priceGroupRepository->get($command->price_groupID) : null,
            $command->is_price_group_fix
        );

        $zapCard->clearZapCardOpt();

        $this->flusher->flush();

        foreach ($opts as $opt) {
            $profit = $command->{'profit_' . $opt->getId()};
            $profit = str_replace(',', '.', $profit);
            if ($profit && floatval($profit) != 0) {
                $zapCard->assignZapCardOpt($opt, $profit);
            }
        }

        $this->flusher->flush();
    }
}

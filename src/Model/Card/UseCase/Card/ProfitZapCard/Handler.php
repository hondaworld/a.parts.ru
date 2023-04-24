<?php

namespace App\Model\Card\UseCase\Card\ProfitZapCard;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;

class Handler
{
    private ZapCardRepository $zapCardRepository;
    private Flusher $flusher;

    public function __construct(ZapCardRepository $zapCardRepository, Flusher $flusher)
    {
        $this->zapCardRepository = $zapCardRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->zapCardRepository->get($command->zapCardID);
        $opts = $command->opts;

        $zapCard->clearZapCardOpt();

        $this->flusher->flush();

        foreach ($opts as $opt) {
            $profit = $command->{'profit_' . $opt->getId()};
            $profit = str_replace(',', '.', $profit);
            if ($profit && floatval($profit) != 0) {
                $zapCard->assignZapCardOpt($opt, $profit);
            }
        }

        $zapCard->updateDateOfOptProfitChanged();

        $this->flusher->flush();
    }
}

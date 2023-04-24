<?php

namespace App\Model\Income\UseCase\Income\Label;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Opt\ZapCardOpt;
use App\Model\Card\Entity\Opt\ZapCardOptRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Sklad\IncomeSkladRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private $incomeRepository;
    private $flusher;
    private $incomeSkladRepository;
    private $zapSkladRepository;

    public function __construct(IncomeRepository $incomeRepository, IncomeSkladRepository $incomeSkladRepository, ZapSkladRepository $zapSkladRepository, Flusher $flusher)
    {
        $this->incomeRepository = $incomeRepository;
        $this->flusher = $flusher;
        $this->incomeSkladRepository = $incomeSkladRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command, Income $income): void
    {
        $income->updateQuantity(
            $command->quantity,
            $command->quantityIn,
            $command->quantityPath,
            $command->reserve,
            $command->quantityReturn
        );

        foreach ($command->incomeSklads as $zapSkladID => $incomeSklad) {

            $incomeSklad = $this->incomeSkladRepository->getBySklad($income, $this->zapSkladRepository->get($zapSkladID));
            $incomeSklad->updateQuantity(
                $command->incomeSklads[$zapSkladID]['quantity'],
                $command->incomeSklads[$zapSkladID]['quantityIn'],
                $command->incomeSklads[$zapSkladID]['quantityPath'],
                $command->incomeSklads[$zapSkladID]['reserve'],
                $command->incomeSklads[$zapSkladID]['quantityReturn']
            );
        }


        $this->flusher->flush();
    }
}

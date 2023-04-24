<?php

namespace App\Model\Income\UseCase\Income\QuantityAll;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Sklad\IncomeSkladRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private Flusher $flusher;
    private IncomeSkladRepository $incomeSkladRepository;
    private ZapSkladRepository $zapSkladRepository;

    public function __construct(IncomeSkladRepository $incomeSkladRepository, ZapSkladRepository $zapSkladRepository, Flusher $flusher)
    {
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
                $command->incomeSklads[$zapSkladID]['quantity'] ?: 0,
                $command->incomeSklads[$zapSkladID]['quantityIn'] ?: 0,
                $command->incomeSklads[$zapSkladID]['quantityPath'] ?: 0,
                $command->incomeSklads[$zapSkladID]['reserve'] ?: 0,
                $command->incomeSklads[$zapSkladID]['quantityReturn'] ?: 0
            );
        }

        $this->flusher->flush();
    }
}

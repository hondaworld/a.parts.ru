<?php

namespace App\Model\Income\UseCase\Income\QuantityChange;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;

class Handler
{
    private IncomeRepository $incomeRepository;
    private Flusher $flusher;

    public function __construct(IncomeRepository $incomeRepository, Flusher $flusher)
    {
        $this->incomeRepository = $incomeRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $income = $this->incomeRepository->get($command->incomeID);

        $incomeNew = Income::cloneFromIncome($income, $command->quantity_new);
        $this->incomeRepository->add($incomeNew);

        $income->splitIncomeQuantity($command->quantity, $incomeNew);
        $this->flusher->flush();
    }
}

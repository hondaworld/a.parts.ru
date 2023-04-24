<?php

namespace App\Model\Income\UseCase\Income\Quantity;

use App\Model\Flusher;
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
        $income->changeIncomeQuantity($command->quantity);
        $this->flusher->flush();
    }
}

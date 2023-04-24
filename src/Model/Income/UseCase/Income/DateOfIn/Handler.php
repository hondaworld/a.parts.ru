<?php

namespace App\Model\Income\UseCase\Income\DateOfIn;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;

class Handler
{
    private $flusher;
    private IncomeRepository $repository;

    public function __construct(
        IncomeRepository $repository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $income = $this->repository->get($command->incomeID);
        $income->updateDateOfIn($command->dateofin);
        $this->flusher->flush();
    }
}

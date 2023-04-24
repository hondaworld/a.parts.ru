<?php

namespace App\Model\Income\UseCase\Income\ZapSklad;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private Flusher $flusher;
    private IncomeRepository $incomeRepository;
    private ZapSkladRepository $zapSkladRepository;

    public function __construct(
        IncomeRepository $incomeRepository,
        ZapSkladRepository $zapSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeRepository = $incomeRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command): void
    {
        foreach ($command->cols as $incomeID) {
            $income = $this->incomeRepository->get($incomeID);
            $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);
            $income->changeSklad($zapSklad);
        }

        $this->flusher->flush();
    }
}

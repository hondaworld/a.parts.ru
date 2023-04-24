<?php

namespace App\Model\Expense\UseCase\Sklad\DeleteIncome;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private Flusher $flusher;
    private ZapSkladRepository $zapSkladRepository;
    private ExpenseSkladRepository $expenseSkladRepository;
    private ZapCardRepository $zapCardRepository;

    public function __construct(
        ZapCardRepository      $zapCardRepository,
        ZapSkladRepository     $zapSkladRepository,
        ExpenseSkladRepository $expenseSkladRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->expenseSkladRepository = $expenseSkladRepository;
        $this->zapCardRepository = $zapCardRepository;
    }

    public function handle(int $zapCardID, int $zapSkladID, ZapSklad $zapSklad_to): void
    {
        $zapCard = $this->zapCardRepository->get($zapCardID);
        $zapSklad = $this->zapSkladRepository->get($zapSkladID);

        $expenseSklads = $this->expenseSkladRepository->findSent($zapCard, $zapSklad, $zapSklad_to);
        foreach ($expenseSklads as $expenseSklad) {
            $expenseSklad->unIncomeFromSklad();
        }

        $this->flusher->flush();
    }
}

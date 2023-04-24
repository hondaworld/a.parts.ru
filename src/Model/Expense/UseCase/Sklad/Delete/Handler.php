<?php

namespace App\Model\Expense\UseCase\Sklad\Delete;

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

    public function handle(string $id, ZapSklad $zapSklad): void
    {
        $arr = explode('_', $id);
        $zapCard = $this->zapCardRepository->get($arr[0]);
        $zapSklad_to = $this->zapSkladRepository->get($arr[1]);

        $expenseSklads = $this->expenseSkladRepository->findAdded($zapCard, $zapSklad, $zapSklad_to);
        foreach ($expenseSklads as $expenseSklad) {
            $expenseSklad->removeReserveByZapSklad($zapSklad);
            $this->expenseSkladRepository->remove($expenseSklad);
        }

        $this->flusher->flush();
    }
}
